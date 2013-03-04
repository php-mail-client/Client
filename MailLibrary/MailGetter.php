<?php

/**
 * @package MailLibrary
 * @author Tomáš Blatný
 */

namespace greeny\MailLib;

use Nette\Object,
	Nette\ArrayHash,
	\Iterator,
	\Countable,
	\ArrayAccess;

/**
 * Basic class for downloading mail from mail server using IMAP.
 */
class MailGetter extends Object implements Iterator, Countable, ArrayAccess
{
	/** @var \Nette\ArrayHash */
	protected $data;

	/** @var bool */
	protected $connected = FALSE;

	/** @var resource */
	protected $connection;

	/** @var bool */
	protected $initialized = FALSE;

	/** @var array */
	protected $mails = array();

	/** @var int */
	protected $iterator = 0;

	/**
	 * Class constructor
	 *
	 * @deprecated
	 * @param string $username  username
	 * @param string $password  password
	 * @param string $host      host
	 * @param int    $port      port
	 * @param bool   $ssl       enable SSL?
	 * @throws MailException Deprecated class
	 */
	public function __construct($username, $password, $host, $port, $ssl = TRUE)
	{
		$this->data = $data = new ArrayHash();
		$data->username = $username;
		$data->password = $password;
		$data->host = $host;
		$data->port = $port;
		$data->ssl = $ssl;

		throw new MailException(__CLASS__." is deprecated in 1.1, use greeny\\Mail\\Connection instead.");
	}

	/**
	 * Class destructor, flushes all changes done to emails
	 */
	public function __destruct()
	{
		$this->flush();
	}

	/**
	 * Connects to mail server. Internal function, do not call it.
	 *
	 * @return MailGetter Provides fluent interface
	 * @throws MailException When error occurs during connection.
	 */
	protected function connect()
	{
		if(!$this->connected) {
			$data = $this->data;
			$ssl = $data->ssl ? '/ssl' : '';
			$this->connection = imap_open('{'.$data->host.':'.$data->port.'/imap'.$ssl.'}INBOX', $data->username, $data->password);
			if($this->connection) {
				$this->connected = TRUE;
			} else {
				throw new MailException("Could not connect to {$data->host}:{$data->port}, using username {$data->username}. ".imap_last_error());
			}
		}
		return $this;
	}

	/**
	 * Initializes list of mails.
	 *
	 * @return MailGetter Provides fluent interface.
	 */
	protected function initialize()
	{
		if(!$this->initialized) {
			$this->connect();
			if($mailIds = imap_search($this->connection, 'ALL', SE_FREE, 'UTF-8')) {
				foreach($mailIds as $mailId) {
					$this->mails[$mailId] = new Mail($this->connection, $mailId);
				}
			}
		}

		return $this;
	}

	/**
	 * Gets mail by id.
	 *
	 * @param int $id Mail id
	 * @return Mail
	 * @throws MailException When email not found.
	 */
	public function getMailById($id)
	{
		$this->initialize();
		if(isset($this->mails[$id])) {
			return $this->mails[$id];
		} else {
			throw new MailException("Mail with id $id doesn't exist.");
		}
	}

	public function deleteMail($id) {
		$this->initialize();
		if(isset($this[$id])) {
			imap_delete($this->connection, $id);
		}
	}

	/**
	 * Gets ids of all found mails
	 *
	 * @return array of int
	 */
	public function getMailIds()
	{
		$return = array();
		foreach($this->initialize()->mails as $id => $m) {
			$return[] = $id;
		}
		return $return;
	}

	public function countMails()
	{
		return $this->initialize()->count($this->mails);
	}

	/**
	 * Flushes changes to mail server.
	 *
	 * @return MailGetter Provides fluent interface.
	 */
	public function flush()
	{
		imap_expunge($this->connection);
		return $this;
	}

	// INTERFACE COUNTABLE

	public function count()
	{
		return $this->countMails();
	}

	// INTERFACE ITERATOR

	public function current()
	{
		return $this->mails[$this->iterator];
	}

	public function next()
	{
		$this->iterator++;
	}

	public function key()
	{
		return $this->iterator;
	}

	public function valid()
	{
		return isset($this->mails[$this->iterator]);
	}

	public function rewind()
	{
		$this->initialize();
		$this->iterator = 1;
	}

	// INTERFACE ARRAYACCESS

	public function offsetExists($offset)
	{
		$this->initialize();
		return isset($this->mails[$offset]);
	}

	public function offsetGet($offset)
	{
		$this->initialize();
		return $this->mails[$offset];
	}

	public function offsetSet($offset, $value)
	{
		throw new MailException("Cannot set readonly mail.");
	}

	public function offsetUnset($offset)
	{
		$this->deleteMail($offset);
	}
}
