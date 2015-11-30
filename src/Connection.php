<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\MailLibrary;

class Connection
{

	/** @var IDriver */
	private $driver;


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

}
