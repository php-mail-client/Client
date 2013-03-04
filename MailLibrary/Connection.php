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

	public function __construct($username, $password, $host, $port, $ssl = TRUE)
	{
		$ssl = $ssl ? '/ssl' : '';
		$this->server = $server = '{'.$host.':'.$port.'/imap'.$ssl.'}';
		$this->connect($username, $password, $server);

		$folders = imap_list($connection = $this->connection, $server, '*');

		if(is_array($folders)) {
			$len = strlen($server);
			foreach($folders as $folder) {
				$this->mailboxes[CharsetConverter::convert(substr($folder, $len), 'utf7-imap')] = new Mailbox();
			}
		} else {
			throw new MailException("No mailboxes found at server '$server'.");
		}
	}

	public function __destruct()
	{
		$this->disconnect();
	}

	public function getConnection()
	{
		return $this->connection;
	}

	protected function connect($username, $password, $host)
	{
		if(!$this->connection = @imap_open($host, $username, $password)) { // @ - To allow throwing exceptions
			throw new MailException("Colud not connect to '$host' using username '$username'.");
		}
	}

	protected function disconnect()
	{
		imap_close($this->connection);
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

	public function getMailbox($name)
	{
		if(isset($this->mailboxes[$name]))
	}
}
