<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\EmailClient\Drivers;

use greeny\EmailClient\Exceptions\DriverException;
use greeny\EmailClient\IDriver;
use greeny\EmailClient\Mailbox;


class ImapDriver implements IDriver
{


	/** @var resource */
	private $resource;

	/** @var bool */
	private $connected = FALSE;

	/** @var string */
	private $server;

	/** @var string */
	private $user;

	/** @var string */
	private $password;

	/** @var bool */
	private $secure;


	/**
	 * @param string $host
	 * @param string $user
	 * @param string $password
	 * @param bool $secure
	 * @param int $port
	 */
	public function __construct($host, $user, $password, $secure = TRUE, $port = NULL)
	{
		if ($port === NULL) {
			$port = $secure ? 993 : 143;
		}
		$ssl = $secure ? '/ssl' : '/novalidate-cert';
		$this->server = '{' . $host . ':' . $port . '/imap' . $ssl . '}';
		$this->user = $user;
		$this->password = $password;
		$this->secure = $secure;
	}


	/**
	 * @inheritdoc
	 */
	public function isConnected($ping = FALSE)
	{
		if ($this->resource && $ping) {
			return imap_ping($this->resource);
		}
		return $this->connected;
	}


	/**
	 * @inheritdoc
	 */
	public function connect()
	{
		$this->resource = @imap_open($this->server, $this->user, $this->password, CL_EXPUNGE);
		if ($this->resource === FALSE) {
			throw DriverException::create('Could not connect to IMAP server', imap_last_error());
		}
		$this->connected = TRUE;
	}


	/**
	 * @inheritdoc
	 */
	public function disconnect()
	{
		if (!@imap_close($this->resource, CL_EXPUNGE)) {
			throw DriverException::create('Could not disconnect from IMAP server', imap_last_error());
		}
		$this->connected = FALSE;
	}


	/**
	 * @inheritdoc
	 */
	public function flush()
	{
		imap_expunge($this->resource);
	}


	/**
	 * @inheritdoc
	 */
	public function getMailboxes()
	{
		$mailboxes = imap_list($this->resource, $this->server, '*');
		if (!is_array($mailboxes)) {
			throw DriverException::create('Could not list mailboxes', imap_last_error());
		}

		return array_map(function ($name) {
			$name = mb_convert_encoding($name, 'UTF8', 'UTF7-IMAP');
			return new Mailbox($this, $name, str_replace($this->server, '', $name));
		}, $mailboxes);
	}


}
