<?php

namespace PhpMailClient;

use PhpMailClient\Drivers\IDriver;

class Connection
{

	/** @var IDriver */
	private $driver;

	/** @var bool */
	private $connected = FALSE;

	/** @var array */
	private $mailboxes = NULL;

	public function __construct(IDriver $driver)
	{
		$this->driver = $driver;
	}

	public function isConnected(): bool
	{
		return $this->connected;
	}

	public function connect(): void
	{
		if (!$this->connected) {
			try {
				$this->driver->connect();
				$this->connected = TRUE;
			} catch (DriverException $e) {
				throw new ConnectionException('Cannot connect to server.', $e->getCode(), $e);
			}
		}
	}

	public function getDriver(): IDriver
	{
		return $this->driver;
	}

	public function flush(): void
	{
		$this->connect();
		$this->driver->flush();
	}

	/**
	 * @return Mailbox[]
	 */
	public function getMailboxes(): array
	{
		if ($this->mailboxes === NULL) {
			$this->initializeMailboxes();
		}
		return $this->mailboxes;
	}

	public function getMailbox(string $name, bool $need = TRUE): ?Mailbox
	{
		$mailboxes = $this->getMailboxes();
		if (isset($mailboxes[$name])) {
			return $mailboxes[$name];
		}

		if ($need) {
			throw new ConnectionException("Mailbox '$name' does not exist.");
		}
		return NULL;
	}

	public function createMailbox(string $name): Mailbox
	{
		$this->connect();
		$this->driver->createMailbox($name);
		$this->mailboxes = NULL;
		return $this->getMailbox($name);
	}

	public function renameMailbox(string $from, string $to): Mailbox
	{
		$this->connect();
		$this->driver->renameMailbox($from, $to);
		$this->mailboxes = NULL;
		return $this->getMailbox($to);
	}

	public function deleteMailbox(string $name): void
	{
		$this->connect();
		$this->driver->deleteMailbox($name);
		$this->mailboxes = NULL;
	}

	public function switchMailbox(string $name): Mailbox
	{
		$this->connect();
		$this->driver->switchMailbox($name);
		return $this->getMailbox($name);
	}

	protected function initializeMailboxes(): void
	{
		$this->connect();
		$this->mailboxes = [];
		foreach ($this->driver->getMailboxes() as $name) {
			$this->mailboxes[$name] = new Mailbox($this, $name);
		}
	}
}
