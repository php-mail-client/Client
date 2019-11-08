<?php

use PhpMailClient\Attachment;
use PhpMailClient\Mail;
use PhpMailClient\DriverException;
use PhpMailClient\Mailbox;
use PhpMailClient\Structures\IStructure;
use PhpMailClient\Drivers\IDriver;

class TestDriver implements IDriver
{

	protected static $filterTable = [
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

	protected $mailboxes = ['x'];

	public function connect(): void
	{
	}

	public function flush(): void
	{
	}

	public function getMailboxes(): array
	{
		return $this->mailboxes;
	}

	public function createMailbox(string $name): void
	{
		$this->mailboxes[] = $name;
	}

	public function renameMailbox(string $from, string $to): void
	{
		foreach ($this->mailboxes as $key => $mailbox) {
			if ($mailbox === $from) {
				$this->mailboxes[$key] = $to;
				return;
			}
		}
	}

	public function deleteMailbox(string $name): void
	{
		foreach ($this->mailboxes as $key => $mailbox) {
			if ($mailbox === $name) {
				unset($this->mailboxes[$key]);
				return;
			}
		}
	}

	public function switchMailbox(string $name): void
	{
	}

	public function getMailIds(array $filters, int $limit = 0, int $offset = 0, string $orderBy = NULL, string $orderType = 'ASC'): array
	{
		if (count($filters)) {
			return [1];
		}
		return [1, 2];
	}

	public function checkFilter(string $key, $value = NULL): void
	{
		if (!in_array($key, array_keys(self::$filterTable))) {
			throw new DriverException("Invalid filter key '$key'.");
		}
		$filtered = self::$filterTable[$key];
		if (strpos($filtered, '%s') !== FALSE) {
			if (!is_string($value)) {
				throw new DriverException("Invalid value type for filter '$key', expected string, got " . gettype($value) . ".");
			}
		} elseif (strpos($filtered, '%d') !== FALSE) {
			if (!($value instanceof DateTime) && !is_int($value) && !strtotime($value)) {
				throw new DriverException("Invalid value type for filter '$key', expected DateTime or timestamp, or textual representation of date, got " . gettype($value) . ".");
			}
		} elseif (strpos($filtered, '%b') !== FALSE) {
			if (!is_bool($value)) {
				throw new DriverException("Invalid value type for filter '$key', expected bool, got " . gettype($value) . ".");
			}
		} elseif ($value !== NULL) {
			throw new DriverException("Cannot assign value to filter '$key'.");
		}
	}

	public function getHeaders(int $mailId): array
	{
		return [
			'name' => md5($mailId),
			'id' => $mailId,
		];
	}

	public function getStructure(int $mailId, Mailbox $mailbox): IStructure
	{
		return new TestStructure;
	}

	public function getBody(int $mailId, array $partIds): string
	{
		return str_repeat($mailId, 10);
	}

	public function getFlags(int $mailId): array
	{
		return[];
	}

	public function setFlag(int $mailId, string $flag, bool $value): void
	{
	}

	public function copyMail(int $mailId, string $toMailbox): void
	{
	}

	public function moveMail(int $mailId, string $toMailbox): void
	{
	}

	public function deleteMail(int $mailId): void
	{
	}

}

class TestStructure implements IStructure
{

	public function getBody(): string
	{
		return str_repeat('body', 10);
	}

	public function getHtmlBody(): string
	{
		return str_repeat('htmlbody', 10);
	}

	public function getTextBody(): string
	{
		return str_repeat('textbody', 10);
	}

	/**
	 * @return Attachment[]
	 */
	public function getAttachments(): array
	{
		return [];
	}

}
