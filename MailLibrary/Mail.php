<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\MailLibrary;

class Mail {
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

	/** @var \greeny\MailLibrary\Connection */
	protected $connection;

	/** @var \greeny\MailLibrary\Mailbox */
	protected $mailbox;

	/** @var int */
	protected $id;

	/** @var array */
	protected $headers = NULL;

	protected static $contactHeaders = array(
		'to',
		'from',
		'cc',
		'bcc',
		'replyTo',
		'deliveredTo',
		'returnPath',
	);

	/**
	 * @param Connection $connection
	 * @param Mailbox    $mailbox
	 * @param int        $id
	 */
	public function __construct(Connection $connection, Mailbox $mailbox, $id)
	{
		$this->connection = $connection;
		$this->mailbox = $mailbox;
		$this->id = $id;
	}

	public function __get($name)
	{
		return $this->getHeader($name);
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return Mailbox
	 */
	public function getMailbox()
	{
		return $this->mailbox;
	}

	/**
	 * @return string[]
	 */
	public function getHeaders()
	{
		$this->headers !== NULL || $this->initializeHeaders();
		return $this->headers;
	}

	/**
	 * @param string $name
	 * @return string
	 */
	public function getHeader($name)
	{
		$this->headers !== NULL || $this->initializeHeaders();
		return $this->headers[$this->formatHeaderName($name)];
	}

	/**
	 * Initializes headers
	 */
	protected function initializeHeaders()
	{
		$this->headers = array();
		$this->connection->getDriver()->switchMailbox($this->mailbox->getName());
		foreach($this->connection->getDriver()->getHeaders($this->id) as $key => $value) {
			$this->headers[$this->formatHeaderName($key)] = $value;
		}
	}

	/**
	 * Formats header name (X-Received-From => xReceivedFrom)
	 *
	 * @param string $name
	 * @return string
	 */
	protected function formatHeaderName($name)
	{
		return lcfirst(preg_replace_callback("~-.~", function($matches){
			return ucfirst(substr($matches[0], 1));
		}, $name));
	}
}
 