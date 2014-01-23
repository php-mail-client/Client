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

	const FLAG_ANSWERED = "\\ANSWERED";
	const FLAG_DELETED = "\\DELETED";
	const FLAG_DRAFT = "\\DRAFT";
	const FLAG_FLAGGED = "\\FLAGGED";
	const FLAG_SEEN = "\\SEEN";

	/** @var \greeny\MailLibrary\Connection */
	protected $connection;

	/** @var \greeny\MailLibrary\Mailbox */
	protected $mailbox;

	/** @var int */
	protected $id;

	/** @var array */
	protected $headers = NULL;

	/** @var \greeny\MailLibrary\Structures\IStructure */
	protected $structure = NULL;

	/** @var array */
	protected $flags = NULL;

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

	public function __isset($name)
	{
		$this->headers !== NULL || $this->initializeHeaders();
		return isset($this->headers[$this->formatHeaderName($name)]);
	}

	/**
	 * Gets header
	 *
	 * @param string $name
	 * @return mixed
	 */
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
	 * @return string
	 */
	public function getBody()
	{
		$this->structure !== NULL || $this->initializeStructure();
		return $this->structure->getBody();
	}

	/**
	 * @return string
	 */
	public function getHtmlBody()
	{
		$this->structure !== NULL || $this->initializeStructure();
		return $this->structure->getHtmlBody();
	}

	/**
	 * @return string
	 */
	public function getTextBody()
	{
		$this->structure !== NULL || $this->initializeStructure();
		return $this->structure->getTextBody();
	}

	/**
	 * @return Attachment[]
	 */
	public function getAttachments()
	{
		$this->structure !== NULL || $this->initializeStructure();
		return $this->structure->getAttachments();
	}

	/**
	 * @return array
	 */
	public function getFlags()
	{
		$this->flags !== NULL || $this->initializeFlags();
		return $this->flags;
	}

	public function setFlags(array $flags, $autoFlush = FALSE)
	{
		$this->connection->getDriver()->switchMailbox($this->mailbox->getName());
		foreach(array(
			Mail::FLAG_ANSWERED,
			Mail::FLAG_DELETED,
			Mail::FLAG_DELETED,
			Mail::FLAG_FLAGGED,
			Mail::FLAG_SEEN,
		) as $flag) {
			if(isset($flags[$flag])) {
				$this->connection->getDriver()->setFlag($this->id, $flag, $flags[$flag]);
			}
		}
		if($autoFlush) {
			$this->connection->getDriver()->flush();
		}
	}

	public function move($toMailbox)
	{
		$this->connection->getDriver()->switchMailbox($this->mailbox->getName());
		$this->connection->getDriver()->moveMail($this->id, $toMailbox);
	}

	public function copy($toMailbox)
	{
		$this->connection->getDriver()->switchMailbox($this->mailbox->getName());
		$this->connection->getDriver()->copyMail($this->id, $toMailbox);
	}

	public function delete()
	{
		$this->connection->getDriver()->switchMailbox($this->mailbox->getName());
		$this->connection->getDriver()->deleteMail($this->id);
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

	protected function initializeStructure()
	{
		$this->connection->getDriver()->switchMailbox($this->mailbox->getName());
		$this->structure = $this->connection->getDriver()->getStructure($this->id, $this->mailbox);
	}

	protected function initializeFlags()
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
	protected function formatHeaderName($name)
	{
		return lcfirst(preg_replace_callback("~-.~", function($matches){
			return ucfirst(substr($matches[0], 1));
		}, $name));
	}
}
 