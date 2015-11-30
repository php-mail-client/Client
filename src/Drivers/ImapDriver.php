<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\MailLibrary\Drivers;

use greeny\MailLibrary\Exceptions\DriverException;
use greeny\MailLibrary\IDriver;


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
		$flags = CL_EXPUNGE;
		if ($this->secure) {
			$flags |= OP_SECURE;
		}
		$this->resource = @imap_open($this->server, $this->user, $this->password, $flags);
		if ($this->resource === FALSE) {
			throw DriverException::create('Could not connect to IMAP server', imap_last_error());
		}
		$this->connected = TRUE;
	}
}
