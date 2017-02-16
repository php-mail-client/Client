<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\MailLibrary\Drivers;

use greeny\MailLibrary\ContactList;
use greeny\MailLibrary\DriverException;
use greeny\MailLibrary\Mailbox;
use greeny\MailLibrary\Structures\IStructure;
use greeny\MailLibrary\Structures\ImapStructure;
use Nette\Utils\Strings;
use greeny\MailLibrary\Mail;
use DateTime;

class ImapDriver implements IDriver
{
	/** @var string */
	protected $username;

	/** @var string */
	protected $password;

	/** @var resource */
	protected $resource;

	/** @var string */
	protected $server;

	/** @var string */
	protected $currentMailbox = NULL;
	
	protected static $filterTable = array(
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
	);

	protected static $contactHeaders = array(
		'to',
		'from',
		'cc',
		'bcc',
	);

	public function __construct($username, $password, $host, $port = 993, $ssl = TRUE)
	{
		$ssl = $ssl ? '/ssl' : '/novalidate-cert';
		$this->server = '{'.$host.':'.$port.'/imap'.$ssl.'}';
		$this->username = $username;
		$this->password = $password;
	}

	/**
	 * Connects to server
	 *
	 * @throws DriverException if connecting fails
	 */
	public function connect()
	{
		if(!$this->resource = @imap_open($this->server, $this->username, $this->password, CL_EXPUNGE)) { // @ - to allow throwing exceptions
			throw new DriverException("Cannot connect to IMAP server: " . imap_last_error());
		}
	}

	/**
	 * Flushes changes to server
	 *
	 * @throws DriverException if flushing fails
	 */
	public function flush()
	{
		imap_expunge($this->resource);
	}

	/**
	 * Gets all mailboxes
	 *
	 * @return array of string
	 * @throws DriverException
	 */
	public function getMailboxes()
	{
		$mailboxes = array();
		$foo = imap_list($this->resource, $this->server, '*');
		if(!$foo) {
			throw new DriverException("Cannot get mailboxes from server: " . imap_last_error());
		}
		foreach($foo as $mailbox) {
			$mailboxes[] = mb_convert_encoding(str_replace($this->server, '', $mailbox), 'UTF8', 'UTF7-IMAP');
		}
		return $mailboxes;
	}

	/**
	 * Creates new mailbox
	 *
	 * @param string $name
	 * @throws DriverException
	 */
	public function createMailbox($name)
	{
		if(!imap_createmailbox($this->resource, $this->server . $name)) {
			throw new DriverException("Cannot create mailbox '$name': " . imap_last_error());
		}
	}

	/**
	 * Renames mailbox
	 *
	 * @param string $from
	 * @param string $to
	 * @throws DriverException
	 */
	public function renameMailbox($from, $to)
	{
		if(!imap_renamemailbox($this->resource, $this->server . $from, $this->server . $to)) {
			throw new DriverException("Cannot rename mailbox from '$from' to '$to': " . imap_last_error());
		}
	}

	/**
	 * Deletes mailbox
	 *
	 * @param string $name
	 * @throws DriverException
	 */
	public function deleteMailbox($name)
	{
		if(!imap_deletemailbox($this->resource, $this->server . $name)) {
			throw new DriverException("Cannot delete mailbox '$name': " . imap_last_error());
		}
	}

	/**
	 * Switches current mailbox
	 *
	 * @param string $name
	 * @throws DriverException
	 */
	public function switchMailbox($name)
	{
		if($name !== $this->currentMailbox) {
			$this->flush();
			if(!imap_reopen($this->resource, $this->server . $name)) {
				throw new DriverException("Cannot switch to mailbox '$name': " . imap_last_error());
			}
			$this->currentMailbox = $name;
		}
	}

	/**
	 * Finds UIDs of mails by filter
	 *
	 * @param array  $filters
	 * @param int    $limit
	 * @param int    $offset
	 * @param int    $orderBy
	 * @param string $orderType
	 * @throws \greeny\MailLibrary\DriverException
	 * @return array of UIDs
	 */
	public function getMailIds(array $filters, $limit = 0, $offset = 0, $orderBy = Mail::ORDER_DATE, $orderType = 'ASC')
	{
		$filter = $this->buildFilters($filters);

		$orderType = $orderType === 'ASC' ? 1 : 0;

		if(!is_array($ids = imap_sort($this->resource, $orderBy, $orderType, SE_UID | SE_NOPREFETCH, $filter, 'UTF-8'))) {
			throw new DriverException("Cannot get mails: " . imap_last_error());
		}

		return $limit === 0 ? $ids : array_slice($ids, $offset, $limit);
	}

	/**
	 * Checks if filter is applicable for this driver
	 *
	 * @param string $key
	 * @param mixed  $value
	 * @throws DriverException
	 */
	public function checkFilter($key, $value = NULL) {
		if(!in_array($key, array_keys(self::$filterTable))) {
			throw new DriverException("Invalid filter key '$key'.");
		}
		$filtered = self::$filterTable[$key];
		if(strpos($filtered, '%s') !== FALSE) {
			if(!is_string($value)) {
				throw new DriverException("Invalid value type for filter '$key', expected string, got ".gettype($value).".");
			}
		} else if(strpos($filtered, '%d') !== FALSE) {
			if(!($value instanceof DateTime) && !is_int($value) && !strtotime($value)) {
				throw new DriverException("Invalid value type for filter '$key', expected DateTime or timestamp, or textual representation of date, got ".gettype($value).".");
			}
		} else if(strpos($filtered, '%b') !== FALSE) {
			if(!is_bool($value)) {
				throw new DriverException("Invalid value type for filter '$key', expected bool, got ".gettype($value).".");
			}
		} else if($value !== NULL) {
			throw new DriverException("Cannot assign value to filter '$key'.");
		}
	}

	/**
	 * Gets mail headers
	 *
	 * @param int $mailId
	 * @return array of name => value
	 */
	public function getHeaders($mailId)
	{
		$raw = imap_fetchheader($this->resource, $mailId, FT_UID);
		$lines = explode("\n", Strings::fixEncoding($raw));
		$headers = array();
		$lastHeader = NULL;
		
		// normalize headers
		foreach($lines as $line) {
			$firstCharacter = mb_substr($line, 0, 1, 'UTF-8'); // todo: correct assumption that string must be UTF-8 encoded?
			if(preg_match('/[\pZ\pC]/u', $firstCharacter) === 1) { // search for UTF-8 whitespaces
				$headers[$lastHeader] .= " " . Strings::trim($line);
			} else {
				$parts = explode(':', $line);
				$name = Strings::trim($parts[0]);
				unset($parts[0]);

				$headers[$name] = Strings::trim(implode(':', $parts));
				$lastHeader = $name;
			}
		}

		foreach($headers as $key => $header) {
			if(trim($key) === '') {
				unset($headers[$key]);
				continue;
			}
			if(strtolower($key) === 'subject') {
				$decoded = imap_mime_header_decode($header);

				$text = '';
				foreach($decoded as $part) {
					if($part->charset !== 'UTF-8' && $part->charset !== 'default') {
						$text .= @mb_convert_encoding($part->text, 'UTF-8', $part->charset); // todo: handle this more properly
					} else {
						$text .= $part->text;
					}
				}

				$headers[$key] = trim($text);
			} /*else if(in_array(strtolower($key), self::$contactHeaders)) {
				$contacts = imap_rfc822_parse_adrlist(imap_utf8(trim($header)), 'UNKNOWN_HOST');
				$list = new ContactList();
				foreach($contacts as $contact) {
					$list->addContact(
						isset($contact->mailbox) ? $contact->mailbox : NULL,
						isset($contact->host) ? $contact->host : NULL,
						isset($contact->personal) ? $contact->personal : NULL,
						isset($contact->adl) ? $contact->adl : NULL
					);
				}
				$list->build();
				$headers[$key] = $list;
			}*/ else {
				$headers[$key] = trim(imap_utf8($header));
			}
		}
		return $headers;
	}

	/**
	 * Creates structure for mail
	 *
	 * @param int     $mailId
	 * @param Mailbox $mailbox
	 * @return IStructure
	 */
	public function getStructure($mailId, Mailbox $mailbox)
	{
		return new ImapStructure($this, imap_fetchstructure($this->resource, $mailId, FT_UID), $mailId, $mailbox);
	}

	/**
	 * Gets part of body
	 *
	 * @param int   $mailId
	 * @param array $data requires id and encoding keys
	 * @return string
	 * @throws \greeny\MailLibrary\DriverException
	 */
	public function getBody($mailId, array $data)
	{
		$body = array();
		foreach($data as $part) {
			assert(is_array($part));
			$dataMessage = ($part['id'] === 0) ? @imap_body($this->resource, $mailId, FT_UID | FT_PEEK) : @imap_fetchbody($this->resource, $mailId, $part['id'], FT_UID | FT_PEEK);
			if($dataMessage === FALSE) {
				throw new DriverException("Cannot read given message part - " . error_get_last()["message"]);
			}
			$encoding = $part['encoding'];
			if($encoding === ImapStructure::ENCODING_BASE64) {
				$dataMessage = base64_decode($dataMessage);
			} else if($encoding === ImapStructure::ENCODING_QUOTED_PRINTABLE) {
				$dataMessage = quoted_printable_decode($dataMessage);
			}

			// todo: other encodings?

			$body[] = $dataMessage;
		}
		return implode('\n\n', $body);
	}

	/**
	 * Gets flags for mail
	 *
	 * @param $mailId
	 * @return array
	 */
	public function getFlags($mailId)
	{
		$data = imap_fetch_overview($this->resource, (string)$mailId, FT_UID);
		reset($data);
		$data = current($data);
		$return = array(
			Mail::FLAG_ANSWERED => FALSE,
			Mail::FLAG_DELETED => FALSE,
			Mail::FLAG_DRAFT => FALSE,
			Mail::FLAG_FLAGGED => FALSE,
			Mail::FLAG_SEEN => FALSE,
		);
		if($data->answered) {
			$return[Mail::FLAG_ANSWERED] = TRUE;
		}
		if($data->deleted) {
			$return[Mail::FLAG_DELETED] = TRUE;
		}
		if($data->draft) {
			$return[Mail::FLAG_DRAFT] = TRUE;
		}
		if($data->flagged) {
			$return[Mail::FLAG_FLAGGED] = TRUE;
		}
		if($data->seen) {
			$return[Mail::FLAG_SEEN] = TRUE;
		}
		return $return;
	}

	/**
	 * Sets one flag for mail
	 *
	 * @param int    $mailId
	 * @param string $flag
	 * @param bool   $value
	 * @throws DriverException
	 */
	public function setFlag($mailId, $flag, $value)
	{
		if($value) {
			if(!imap_setflag_full($this->resource, $mailId, $flag, ST_UID)) {
				throw new DriverException("Cannot set flag '$flag': ".imap_last_error());
			}
		} else {
			if(!imap_clearflag_full($this->resource, $mailId, $flag, ST_UID)) {
				throw new DriverException("Cannot unset flag '$flag': ".imap_last_error());
			}
		}
	}

	/**
	 * Copies mail to another mailbox
	 * @param int    $mailId
	 * @param string $toMailbox
	 * @throws DriverException
	 */
	public function copyMail($mailId, $toMailbox) {
		if(!imap_mail_copy($this->resource, $mailId, /*$this->server .*/ $this->encodeMailboxName($toMailbox), CP_UID)) {
			throw new DriverException("Cannot copy mail to mailbox '$toMailbox': ".imap_last_error());
		}
	}

	/**
	 * Moves mail to another mailbox
	 * @param int    $mailId
	 * @param string $toMailbox
	 * @throws DriverException
	 */
	public function moveMail($mailId, $toMailbox) {
		if(!imap_mail_move($this->resource, $mailId, /*$this->server .*/ $this->encodeMailboxName($toMailbox), CP_UID)) {
			throw new DriverException("Cannot copy mail to mailbox '$toMailbox': ".imap_last_error());
		}
	}

	/**
	 * Deletes mail
	 * @param int $mailId
	 * @throws DriverException
	 */
	public function deleteMail($mailId) {
		if(!imap_delete($this->resource, $mailId, FT_UID)) {
			throw new DriverException("Cannot delete mail: ".imap_last_error());
		}
	}

	/**
	 * Builds filter string from filters
	 *
	 * @param array $filters
	 * @return string
	 */
	protected function buildFilters(array $filters)
	{
		$return = array();
		foreach($filters as $filter) {
			$key = self::$filterTable[$filter['key']];
			$value = $filter['value'];

			if(strpos($key, '%s') !== FALSE) {
				$data = str_replace('%s', str_replace('"', '', (string)$value), $key);
			} else if(strpos($key, '%d') !== FALSE) {
				if($value instanceof DateTime) {
					$timestamp = $value->getTimestamp();
				} else if(is_string($value)) {
					$timestamp = strtotime($value) ?: Time();
				} else {
					$timestamp = (int)$value;
				}
				$data = str_replace('%d', date("d M Y", $timestamp), $key);
			} else if(strpos($key, '%b') !== FALSE) {
				$data = str_replace('%b', ((bool)$value ? '' : 'UN'), $key);
			} else {
				$data = $key;
			}
			$return[] = $data;
		}
		return implode(' ', $return);
	}

	/**
	 * Builds list from ids array
	 *
	 * @param array $ids
	 * @return string
	 */
	protected function buildIdList(array $ids)
	{
		sort($ids);
		return implode(',', $ids);
	}

	/**
	 * Converts mailbox name encoding as defined in IMAP RFC 2060.
	 *
	 * @param $name
	 * @return string
	 */
	protected function encodeMailboxName($name)
	{
		return mb_convert_encoding($name, 'UTF7-IMAP', 'UTF-8');
	}

}
