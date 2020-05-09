<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\MailLibrary;

use greeny\MailLibrary\Drivers\IDriver;

class Connection
{
	/** @var IDriver */
	protected $driver;

	/** @var bool */
	protected $connected = FALSE;

	/** @var array */
	protected $mailboxes = NULL;

	/**
	 * @param IDriver $driver
	 */
	public function __construct(IDriver $driver)
	{
		$this->driver = $driver;
	}

	/**
	 * @return bool
	 */
	public function isConnected()
	{
		return $this->connected;
	}

	/**
	 * Connects to the server
	 * @return Connection
	 * @throws ConnectionException
	 */
	public function connect()
	{
		if(!$this->connected) {
			try {
				$this->driver->connect();
				$this->connected = TRUE;
			} catch(DriverException $e) {
				throw new ConnectionException("Cannot connect to server.", $e->getCode(), $e);
			}
		}
		return $this;
	}

	/**
	 * @return IDriver
	 */
	public function getDriver()
	{
		return $this->driver;
	}

	/**
	 * Flushes changes to server
	 * @return Connection
	 * @throws DriverException
	 */
	public function flush()
	{
		$this->connected || $this->connect();
		$this->driver->flush();
		return $this;
	}

	/**
	 * Gets all mailboxes
	 * @return Mailbox[]
	 */
	public function getMailboxes()
	{
		$this->mailboxes !== NULL || $this->initializeMailboxes();
		return $this->mailboxes;
	}

	/**
	 * Gets mailbox by name
	 * @param $name
	 * @return Mailbox
	 * @throws ConnectionException
	 */
	public function getMailbox($name)
	{
		$this->mailboxes !== NULL || $this->initializeMailboxes();
		if(isset($this->mailboxes[$name])) {
			return $this->mailboxes[$name];
		} else {
			throw new ConnectionException("Mailbox '$name' does not exist.");
		}
	}

	/**
	 * Creates mailbox
	 * @param string $name
	 * @return Mailbox
	 * @throws DriverException
	 */
	public function createMailbox($name)
	{
		$this->connected || $this->connect();
		$this->driver->createMailbox($name);
		$this->mailboxes = NULL;
		return $this->getMailbox($name);
	}

	/**
	 * Renames mailbox
	 * @param string $from
	 * @param string $to
	 * @return Mailbox
	 * @throws DriverException
	 */
	public function renameMailbox($from, $to)
	{
		$this->connected || $this->connect();
		$this->driver->renameMailbox($from, $to);
		$this->mailboxes = NULL;
		return $this->getMailbox($to);
	}

	/**
	 * Deletes mailbox
	 * @param string $name
	 * @throws DriverException
	 */
	public function deleteMailbox($name)
	{
		$this->connected || $this->connect();
		$this->driver->deleteMailbox($name);
		$this->mailboxes = NULL;
	}

	/**
	 * Switches currently using mailbox
	 * @param string $name
	 * @throws DriverException
	 * @return Mailbox
	 */
	public function switchMailbox($name)
	{
		$this->connected || $this->connect();
		$this->driver->switchMailbox($name);
		return $this->getMailbox($name);
	}

	/**
	 * Initializes mailboxes
	 */
	protected function initializeMailboxes()
	{
		$this->connected || $this->connect();
		$this->mailboxes = array();
		foreach($this->driver->getMailboxes() as $name) {
			$this->mailboxes[$name] = new Mailbox($this, $name);
		}
	}
}