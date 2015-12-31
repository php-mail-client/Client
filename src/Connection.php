<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\EmailClient;

class Connection
{

	/** @var IDriver */
	private $driver;

	/** @var Mailbox[] */
	private $mailboxes;


	/**
	 * @param IDriver $driver
	 */
	public function __construct(IDriver $driver)
	{
		$this->driver = $driver;
	}


	/**
	 * Returns used driver
	 *
	 * @return IDriver
	 */
	public function getDriver()
	{
		return $this->driver;
	}


	/**
	 * Connects to server
	 *
	 * @return $this
	 */
	public function connect()
	{
		if (!$this->driver->isConnected()) {
			$this->driver->connect();
		}

		return $this;
	}


	/**
	 * Forces check of connection
	 *
	 * @return bool
	 */
	public function isConnected()
	{
		return $this->driver->isConnected(TRUE);
	}


	/**
	 * Disconnects from server
	 */
	public function disconnect()
	{
		if ($this->driver->isConnected()) {
			$this->driver->disconnect();
		}
	}


	/**
	 * Gets all mailboxes from server
	 *
	 * @return Mailbox[]
	 */
	public function getMailboxes()
	{
		if (!$this->mailboxes) {
			$this->mailboxes = $this->driver->getMailboxes();
		}
		return $this->mailboxes;
	}

}
