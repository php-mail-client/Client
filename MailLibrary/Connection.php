<?php
/**
 * @package MailLibrary
 * @author Tomáš Blatný
 */

namespace greeny\MailLib;

use Nette\Object;

/**
 * Represents connection to mail server.
 */
class Connection extends Object implements IConnection
{
	/** @var resource */
	protected $connection;

	/** @var string */
	protected $server;

	/** @var array of Mailbox */
	protected $mailboxes = array();

	/** @var string the mailbox, that is currently using */
	public $usingMailbox;

	/**
	 * @param string    $username   username
	 * @param string    $password   password
	 * @param string    $host       host (e.g. imap.example.com)
	 * @param int       $port       port to use (e.g. 992)
	 * @param bool      $ssl        enable ssl?
	 * @throws MailException when connection not created
	 */
	public function __construct($username, $password, $host, $port, $ssl = TRUE)
	{
		$ssl = $ssl ? '/ssl' : '';
		$this->server = $server = '{'.$host.':'.$port.'/imap'.$ssl.'}';
		$this->connect($username, $password, $server);

		$folders = imap_list($connection = $this->connection, $server, '*');

		if(is_array($folders)) {
			$len = strlen($server);
			foreach($folders as $folder) {
				$name = CharsetConverter::convert(substr($folder, $len), 'utf7-imap');
				$this->mailboxes[$name] = new Mailbox($this, $name);
			}
		} else {
			throw new MailException("No mailboxes found at server '$server'.");
		}
	}

	/**
	 * Flushes changes to server and disconects.
	 */
	public function __destruct()
	{
		$this->flush()
			->disconnect();
	}

	/**
	 * Gets connection
	 *
	 * @return resource
	 */
	public function getConnection()
	{
		return $this->connection;
	}

	/**
	 * Gets server string
	 *
	 * @return string
	 */
	public function getServer()
	{
		return $this->server;
	}

	/**
	 * Connects to mail server. Internal function, do not call it directly.
	 *
	 * @param string    $username   username
	 * @param string    $password   password
	 * @param string    $host       imap host string
	 * @throws MailException when there is an error in connection
	 * @return \greeny\MailLib\Connection Provides fluent interface.
	 */
	protected function connect($username, $password, $host)
	{
		if(!$this->connection = @imap_open($host, $username, $password)) { // @ - To allow throwing exceptions
			throw new MailException("Colud not connect to '$host' using username '$username'.");
		}
		return $this;
	}

	/**
	 * Disconnects from server.
	 *
	 * @return \greeny\MailLib\Connection Provides fluent interface.
	 */
	public function disconnect()
	{
		imap_close($this->connection);
		return $this;
	}

	/**
	 * Flushes changes to server.
	 *
	 * @return \greeny\MailLib\Connection Provides fluent interface.
	 */
	public function flush()
	{
		imap_expunge($this->connection);
		return $this;
	}

	/**
	 * Returns list of mailboxes
	 *
	 * @return array of Mailbox
	 */
	public function getMailboxes()
	{
		return $this->mailboxes;
	}

	/**
	 * @param string    $name   name of Mailbox
	 * @return Mailbox
	 * @throws MailException whne mailbox not found
	 */
	public function getMailbox($name)
	{
		if(isset($this->mailboxes[$name])) {
			return $this->mailboxes[$name];
		} else {
			throw new MailException("Mailbox $name doesn't exist.");
		}
	}
}
