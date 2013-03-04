<?php
/**
 * @package MailLibrary
 * @author Tomáš Blatný
 */

namespace greeny\MailLib;

use Nette\Object;

/**
 * Represents one Mailbox
 */
class Mailbox extends Object implements IMailbox
{
	/** @var \greeny\MailLib\Connection */
	protected $connection;

	/** @var string */
	protected $name;

	/**
	 * @param Connection    $connection connection class
	 * @param string        $name       name of inbox
	 */
	public function __construct(Connection $connection, $name)
	{
		$this->connection = $connection;
		$this->name = $name;
	}

	/**
	 * Returns mailbox name
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Counts number of mails
	 *
	 * @return int
	 */
	public function countMails()
	{
		// TODO: Implement countMails() method.
	}

	/**
	 * Returns all mails
	 *
	 * @return array of Mail
	 */
	public function getMails()
	{
		// TODO: Implement getMails() method.
	}

	/**
	 * Is called whenever this mailbox is used to reopen connection.
	 *
	 * @return Mailbox Provides fluent interface.
	 * @throws MailException when there is an error in reopening
	 */
	protected function using()
	{
		if($this->connection->usingMailbox !== $this->name) {
			$this->connection->usingMailbox = $this->name;
			if(!imap_reopen($this->connection->getConnection(), $this->connection->getServer(), $this->name)) {
				throw new MailException("Cannot open mailbox '{$this->name}'.");
			}
		}
		return $this;
	}
}
