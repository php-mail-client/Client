<?php

namespace PhpMailClient\Drivers;

use PhpMailClient\ContactList;
use PhpMailClient\DriverException;
use PhpMailClient\Mailbox;
use PhpMailClient\Structures\IStructure;
use PhpMailClient\Structures\ImapStructure;
use PhpMailClient\Mail;
use DateTime;

class ImapDriver implements IDriver
{

	/** @var string */
	private $username;

	/** @var string */
	private $password;

	/** @var resource */
	private $resource;

	/** @var string */
	private $server;

	/** @var string */
	private $currentMailbox = NULL;

	private static $filterTable = [
		Mail::ANSWERED => '%bANSWERED',
		Mail::BCC => 'BCC "%s"',
		Mail::BEFORE => 'BEFORE "%d"',
		Mail::BODY => 'BODY "%s"',
		Mail::CC => 'CC "%s"',
		Mail::DELETED => '%bDELETED',
		Mail::FLAGGED => '%bFLAGGED',
		Mail::FROM => 'FROM "%s"',
		Mail::KEYWORD => 'KEYWORD "%s"',
		Mail::NEW_MESSAGES => 'NEW',
		Mail::NOT_KEYWORD => 'UNKEYWORD "%s"',
		Mail::OLD_MESSAGES => 'OLD',
		Mail::ON => 'ON "%d"',
		Mail::RECENT => 'RECENT',
		Mail::SEEN => '%bSEEN',
		Mail::SINCE => 'SINCE "%d"',
		Mail::SUBJECT => 'SUBJECT "%s"',
		Mail::TEXT => 'TEXT "%s"',
		Mail::TO => 'TO "%s"',
	];

	private static $contactHeaders = [
		'to',
		'from',
		'cc',
		'bcc',
	];

	public function __construct($username, $password, $host, $port = 993, $ssl = TRUE)
	{
		$this->server = '{' . $host . ':' . $port . '/imap' . ($ssl ? '/ssl' : '/novalidate-cert') . '}';
		$this->username = $username;
		$this->password = $password;
	}

	public function connect(): void
	{
		if (!$this->resource = @imap_open($this->server, $this->username, $this->password, CL_EXPUNGE)) { // @ - to allow throwing exceptions
			throw new DriverException('Cannot connect to IMAP server: ' . imap_last_error());
		}
	}

	public function flush(): void
	{
		imap_expunge($this->resource);
	}

	public function getMailboxes(): array
	{
		$mailboxes = [];
		$foo = imap_list($this->resource, $this->server, '*');
		if (!$foo) {
			throw new DriverException('Cannot get mailboxes from server: ' . imap_last_error());
		}
		foreach ($foo as $mailbox) {
			$mailboxes[] = mb_convert_encoding(str_replace($this->server, '', $mailbox), 'UTF8', 'UTF7-IMAP');
		}
		return $mailboxes;
	}

	public function createMailbox(string $name): void
	{
		if (!imap_createmailbox($this->resource, $this->server . $name)) {
			throw new DriverException(sprintf('Cannot create mailbox %s: %s', $name, imap_last_error()));
		}
	}

	public function renameMailbox(string $from, string $to): void
	{
		if (!imap_renamemailbox($this->resource, $this->server . $from, $this->server . $to)) {
			throw new DriverException(sprintf("Cannot rename mailbox from '%s' to '%s': %s", $from, $to, imap_last_error()));
		}
	}

	public function deleteMailbox(string $name): void
	{
		if (!imap_deletemailbox($this->resource, $this->server . $name)) {
			throw new DriverException(sprintf('Cannot delete mailbox %s: %s', $name, imap_last_error()));
		}
	}

	public function switchMailbox(string $name): void
	{
		if ($name !== $this->currentMailbox) {
			$this->flush();
			if (!imap_reopen($this->resource, $this->server . $name)) {
				throw new DriverException(sprintf('Cannot switch to mailbox %s: %s', $name, imap_last_error()));
			}
			$this->currentMailbox = $name;
		}
	}

	public function getMailIds(array $filters, int $limit = 0, int $offset = 0, ?string $orderBy = NULL, string $orderType = 'ASC'): array
	{
		$filter = $this->buildFilters($filters);

		if (!is_array($ids = imap_sort($this->resource, $orderBy, $orderType === 'ASC' ? 1 : 0, SE_UID | SE_NOPREFETCH, $filter, 'UTF-8'))) {
			throw new DriverException(sprintf('Cannot get mails: %s', imap_last_error()));
		}

		return $limit === 0 ? $ids : array_slice($ids, $offset, $limit);
	}

	public function checkFilter(string $key, $value = NULL): void
	{
		if (!array_key_exists($key, self::$filterTable)) {
			throw new DriverException(sprintf('Invalid filter key %s.', $key));
		}
		$filtered = self::$filterTable[$key];
		if (strpos($filtered, '%s') !== FALSE) {
			if (!is_string($value)) {
				throw new DriverException(sprintf("Invalid value type for filter '%s', expected string, got %s.", $key, gettype($value)));
			}
		} elseif (strpos($filtered, '%d') !== FALSE) {
			if (!($value instanceof DateTime) && !is_int($value) && !strtotime($value)) {
				throw new DriverException(sprintf('Invalid value type for filter %s, expected DateTime or timestamp, or textual representation of date, got %s.', $key, gettype($value)));
			}
		} elseif (strpos($filtered, '%b') !== FALSE) {
			if (!is_bool($value)) {
				throw new DriverException(sprintf('Invalid value type for filter %s, expected bool, got %s.', $key, gettype($value)));
			}
		} elseif ($value !== NULL) {
			throw new DriverException(sprintf('Cannot assign value to filter %s.', $key));
		}
	}

	public function getHeaders(int $mailId): array
	{
		$raw = imap_fetchheader($this->resource, $mailId, FT_UID);
		$lines = explode("\n", $raw);
		$headers = [];
		$lastHeader = NULL;
		foreach ($lines as $line) {
			if (mb_strpos($line, ' ', 'UTF-8') === 0) {
				$headers[$lastHeader] .= $line;
			} else {
				$parts = explode(':', $line);
				$name = $parts[0];
				unset($parts[0]);

				$headers[$name] = implode(':', $parts);
				$lastHeader = $name;
			}
		}

		foreach ($headers as $key => $header) {
			if (trim($key) === '') {
				unset($headers[$key]);
				continue;
			}
			if (strtolower($key) === 'subject') {
				$decoded = imap_mime_header_decode($header);

				$text = '';
				foreach ($decoded as $part) {
					if ($part->charset !== 'UTF-8' && $part->charset !== 'default') {
						$text .= mb_convert_encoding($part->text, 'UTF-8', $part->charset);
					} else {
						$text .= $part->text;
					}
				}

				$headers[$key] = trim($text);
			} elseif (in_array(strtolower($key), self::$contactHeaders, TRUE)) {
				$contacts = imap_rfc822_parse_adrlist(imap_utf8(trim($header)), 'UNKNOWN_HOST');
				$list = new ContactList;
				foreach ($contacts as $contact) {
					$list->addContact(
						isset($contact->mailbox) ? $contact->mailbox : NULL,
						isset($contact->host) ? $contact->host : NULL,
						isset($contact->personal) ? $contact->personal : NULL,
						isset($contact->adl) ? $contact->adl : NULL
					);
				}
				$list->build();
				$headers[$key] = $list;
			} else {
				$headers[$key] = trim(imap_utf8($header));
			}
		}
		return $headers;
	}

	public function getStructure(int $mailId, Mailbox $mailbox): IStructure
	{
		return new ImapStructure($this, imap_fetchstructure($this->resource, $mailId, FT_UID), $mailId, $mailbox);
	}

	public function getBody(int $mailId, array $data): string
	{
		$body = [];
		foreach ($data as $part) {
			$data = ($part['id'] == 0) ? imap_body($this->resource, $mailId, FT_UID | FT_PEEK) : imap_fetchbody($this->resource, $mailId, $part['id'], FT_UID | FT_PEEK);
			$encoding = $part['encoding'];
			if ($encoding === ImapStructure::ENCODING_BASE64) {
				$data = base64_decode($data);
			} elseif ($encoding === ImapStructure::ENCODING_QUOTED_PRINTABLE) {
				$data = quoted_printable_decode($data);
			}

			$body[] = $data;
		}
		return implode('\n\n', $body);
	}

	public function getFlags(int $mailId): array
	{
		$data = imap_fetch_overview($this->resource, (string)$mailId, FT_UID);
		reset($data);
		$data = current($data);
		$return = [
			Mail::FLAG_ANSWERED => FALSE,
			Mail::FLAG_DELETED => FALSE,
			Mail::FLAG_DRAFT => FALSE,
			Mail::FLAG_FLAGGED => FALSE,
			Mail::FLAG_SEEN => FALSE,
		];
		if ($data->answered) {
			$return[Mail::FLAG_ANSWERED] = TRUE;
		}
		if ($data->deleted) {
			$return[Mail::FLAG_DELETED] = TRUE;
		}
		if ($data->draft) {
			$return[Mail::FLAG_DRAFT] = TRUE;
		}
		if ($data->flagged) {
			$return[Mail::FLAG_FLAGGED] = TRUE;
		}
		if ($data->seen) {
			$return[Mail::FLAG_SEEN] = TRUE;
		}
		return $return;
	}

	public function setFlag(int $mailId, string $flag, bool $value): void
	{
		if ($value) {
			if (!imap_setflag_full($this->resource, $mailId, $flag, ST_UID)) {
				throw new DriverException(sprintf('Cannot set flag %s: %s', $flag, imap_last_error()));
			}
		} else {
			if (!imap_clearflag_full($this->resource, $mailId, $flag, ST_UID)) {
				throw new DriverException(sprintf('Cannot unset flag %s: %s', $flag, imap_last_error()));
			}
		}
	}

	public function copyMail(int $mailId, string $toMailbox): void
	{
		if (!imap_mail_copy($this->resource, $mailId, $this->server . $this->encodeMailboxName($toMailbox), CP_UID)) {
			throw new DriverException(sprintf("Cannot copy mail to mailbox %s: %s", $toMailbox, imap_last_error()));
		}
	}

	public function moveMail(int $mailId, string $toMailbox): void
	{
		if (!imap_mail_move($this->resource, $mailId, $this->server . $this->encodeMailboxName($toMailbox), CP_UID)) {
			throw new DriverException(sprintf('Cannot copy mail to mailbox %s: %s', $toMailbox, imap_last_error()));
		}
	}

	public function deleteMail(int $mailId): void
	{
		if (!imap_delete($this->resource, $mailId, FT_UID)) {
			throw new DriverException(sprintf('Cannot delete mail: %s', imap_last_error()));
		}
	}

	/**
	 * Builds filter string from filters
	 *
	 * @param array $filters
	 * @return string
	 */
	private function buildFilters(array $filters): string
	{
		$return = [];
		foreach ($filters as $filter) {
			$key = self::$filterTable[$filter['key']];
			$value = $filter['value'];

			if (strpos($key, '%s') !== FALSE) {
				$data = str_replace('%s', str_replace('"', '', (string)$value), $key);
			} elseif (strpos($key, '%d') !== FALSE) {
				if ($value instanceof DateTime) {
					$timestamp = $value->getTimestamp();
				} elseif (is_string($value)) {
					$timestamp = strtotime($value) ?: Time();
				} else {
					$timestamp = (int) $value;
				}
				$data = str_replace('%d', date('d M Y', $timestamp), $key);
			} elseif (strpos($key, '%b') !== FALSE) {
				$data = str_replace('%b', ((bool) $value ? '' : 'UN'), $key);
			} else {
				$data = $key;
			}
			$return[] = $data;
		}
		return implode(' ', $return);
	}

	private function encodeMailboxName(string $name): string
	{
		return mb_convert_encoding($name, 'UTF7-IMAP', 'UTF-8');
	}

}
