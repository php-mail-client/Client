<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\MailLibrary;

class Mailbox {
	/** @var \greeny\MailLibrary\Connection */
	protected $connection;

	/** @var string */
	protected $name;

	/**
	 * @param Connection $connection
	 * @param string     $name
	 */
	public function __construct(Connection $connection, $name)
	{
		$this->connection = $connection;
		$this->name = $name;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getMails()
	{
		return new Selection($this->connection, $this);
	}
}
 