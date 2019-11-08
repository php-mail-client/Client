<?php

namespace PhpMailClient;

use PhpMailClient\Structures\IStructure;

class Mail
{

	const ANSWERED = 'ANSWERED';
	const BCC = 'BCC';
	const BEFORE = 'BEFORE';
	const BODY = 'BODY';
	const CC = 'CC';
	const DELETED = 'DELETED';
	const FLAGGED = 'FLAGGED';
	const FROM = 'FROM';
	const KEYWORD = 'KEYWORD';
	const NEW_MESSAGES = 'NEW';
	const NOT_KEYWORD = 'UNKEYWORD';
	const OLD_MESSAGES = 'OLD';
	const ON = 'ON';
	const RECENT = 'RECENT';
	const SEEN = 'SEEN';
	const SINCE = 'SINCE';
	const SUBJECT = 'SUBJECT';
	const TEXT = 'TEXT';
	const TO = 'TO';

	const FLAG_ANSWERED = '\\ANSWERED';
	const FLAG_DELETED = '\\DELETED';
	const FLAG_DRAFT = '\\DRAFT';
	const FLAG_FLAGGED = '\\FLAGGED';
	const FLAG_SEEN = '\\SEEN';

	const ORDER_DATE = SORTARRIVAL;
	const ORDER_FROM = SORTFROM;
	const ORDER_SUBJECT = SORTSUBJECT;
	const ORDER_TO = SORTTO;
	const ORDER_CC = SORTCC;
	const ORDER_SIZE = SORTSIZE;

	/** @var Connection */
	protected $connection;

	/** @var Mailbox */
	protected $mailbox;

	/** @var int */
	protected $id;

	/** @var array */
	protected $headers = NULL;

	/** @var IStructure */
	protected $structure = NULL;

	/** @var array */
	protected $flags = NULL;

	public function __construct(Connection $connection, Mailbox $mailbox, int $id)
	{
		$this->connection = $connection;
		$this->mailbox = $mailbox;
		$this->id = $id;
	}

	public function __isset(string $name): bool
	{
		$this->headers !== NULL || $this->initializeHeaders();
		return isset($this->headers[$this->formatHeaderName($name)]);
	}

	public function __get(string $name): string
	{
		return $this->getHeader($name);
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function getMailbox(): Mailbox
	{
		return $this->mailbox;
	}

	/**
	 * @return string[]
	 */
	public function getHeaders(): array
	{
		$this->headers !== NULL || $this->initializeHeaders();
		return $this->headers;
	}

	public function getHeader(string $name): string
	{
		$this->headers !== NULL || $this->initializeHeaders();
		return $this->headers[$this->formatHeaderName($name)];
	}

	public function getSender(): ?Contact
	{
		$from = $this->getHeader('from');
		if ($from) {
			$contacts = $from->getContactsObjects();
			return (count($contacts) ? $contacts[0] : NULL);
		} else {
			return NULL;
		}
	}

	public function getBody(): string
	{
		$this->structure !== NULL || $this->initializeStructure();
		return $this->structure->getBody();
	}

	public function getHtmlBody(): string
	{
		$this->structure !== NULL || $this->initializeStructure();
		return $this->structure->getHtmlBody();
	}

	public function getTextBody(): string
	{
		$this->structure !== NULL || $this->initializeStructure();
		return $this->structure->getTextBody();
	}

	/**
	 * @return Attachment[]
	 */
	public function getAttachments(): array
	{
		$this->structure !== NULL || $this->initializeStructure();
		return $this->structure->getAttachments();
	}

	public function getFlags(): array
	{
		$this->flags !== NULL || $this->initializeFlags();
		return $this->flags;
	}

	public function setFlags(array $flags, bool $flush = FALSE): void
	{
		$this->connection->getDriver()->switchMailbox($this->mailbox->getName());
		foreach ([
			self::FLAG_ANSWERED,
			self::FLAG_DELETED,
			self::FLAG_DELETED,
			self::FLAG_FLAGGED,
			self::FLAG_SEEN,
		] as $flag) {
			if (isset($flags[$flag])) {
				$this->connection->getDriver()->setFlag($this->id, $flag, $flags[$flag]);
			}
		}
		if ($flush) {
			$this->connection->getDriver()->flush();
		}
	}

	public function move(string $toMailbox): void
	{
		$this->connection->getDriver()->switchMailbox($this->mailbox->getName());
		$this->connection->getDriver()->moveMail($this->id, $toMailbox);
	}

	public function copy(string $toMailbox): void
	{
		$this->connection->getDriver()->switchMailbox($this->mailbox->getName());
		$this->connection->getDriver()->copyMail($this->id, $toMailbox);
	}

	public function delete(): void
	{
		$this->connection->getDriver()->switchMailbox($this->mailbox->getName());
		$this->connection->getDriver()->deleteMail($this->id);
	}

	/**
	 * Initializes headers
	 */
	protected function initializeHeaders(): void
	{
		$this->headers = [];
		$this->connection->getDriver()->switchMailbox($this->mailbox->getName());
		foreach ($this->connection->getDriver()->getHeaders($this->id) as $key => $value) {
			$this->headers[$this->formatHeaderName($key)] = $value;
		}
	}

	protected function initializeStructure(): void
	{
		$this->connection->getDriver()->switchMailbox($this->mailbox->getName());
		$this->structure = $this->connection->getDriver()->getStructure($this->id, $this->mailbox);
	}

	protected function initializeFlags(): void
	{
		$this->connection->getDriver()->switchMailbox($this->mailbox->getName());
		$this->flags = $this->connection->getDriver()->getFlags($this->id);
	}

	/**
	 * Formats header name (X-Received-From => xReceivedFrom)
	 *
	 * @param string $name
	 * @return string
	 */
	protected function formatHeaderName(string $name): string
	{
		return lcfirst(preg_replace_callback('~-.~', function ($matches) {
			return ucfirst(substr($matches[0], 1));
		}, $name));
	}
}
