<?php
/**
 * @package MailLibrary
 * @author Tomáš Blatný
 */

namespace greeny\MailLib;

use Nette\Object,
	Nette\ArrayHash;

/**
 * Represents one Mailbox
 */
class Mailbox extends Object implements IMailbox
{
	/** @var \greeny\MailLib\Connection */
	protected $connection;

	/** @var string */
	protected $name;

	/** @var \Nette\ArrayHash */
	protected $data;

	/**
	 * @param Connection    $connection connection class
	 * @param string        $name       name of inbox
	 */
	public function __construct(Connection $connection, $name)
	{
		$this->connection = $connection;
		$this->name = $name;
		$this->data = $data = new ArrayHash();
	}

	public function getConnection() {
		return $this->connection;
	}

	public function getResource() {
		return $this->connection->getResource();
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
	 * Returns full mailbox name
	 *
	 * @return string
	 */
	public function getFullName() {
		return $this->connection->getServer().$this->name;
	}

	public function where($key, $value) {
		$s = new MailSelection($this);
		return $s->where($key, $value);
	}

	public function limit($limit) {
		$s = new MailSelection($this);
		return $s->limit($limit);
	}

	public function offset($offset) {
		$s = new MailSelection($this);
		return $s->offset($offset);
	}

	public function order($key, $order) {
		$s = new MailSelection($this);
		return $s->order($key, $order);
	}

	public function count() {
		$s = new MailSelection($this);
		return $s->count();
	}

	/**
	 * Counts number of mails
	 *
	 * @return int
	 */
	public function countMails()
	{
		if(isset($this->data->count)) {
			return $this->data->count;
		} else {
			$this->using();
			return $this->data->count = imap_check($this->getResource())->Nmsgs;
		}
	}

	/**
	 * Returns all mails
	 *
	 * @return array of Mail
	 */
	public function getAllMails()
	{
		if(isset($this->data->mails)) {
			return $this->data->mails;
		} else {
			$this->using()->data->mails = array();
			for($i = 1; $i <= $this->countMails(); $i++) {
				$this->data->mails[$i] = new Mail($this->connection, $i);
			}
			return $this->data->mails;
		}
	}

	/**
	 * Returns mail with sequence id $id.
	 *
	 * @param int   $id     sequence id
	 * @return Mail
	 * @throws MailException when mail not found.
	 */
	public function getMailById($id) {
		if(isset($this->data->mails)) {
			if(isset($this->data->mails[$id])) {
				return $this->data->mails[$id];
			} else {
				throw new MailException("Mail with id $id not found.");
			}
		} else {
			$this->getAllMails();
			return $this->getMailById($id);
		}
	}

	/**
	 * Forces mailbox to update
	 *
	 * @return Mailbox Provides fluent interface.
	 */
	public function update()
	{
		$this->data->count = imap_check($this->getResource())->Nmsgs;

		return $this->using();
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
			if(!imap_reopen($this->getResource(), CharsetConverter::convert($this->getFullName(), 'utf-8', 'utf7-imap'))) {
				throw new MailException("Cannot open mailbox '{$this->name}'.");
			}
		}
		return $this;
	}
}
