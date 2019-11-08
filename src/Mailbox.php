<?php

namespace PhpMailClient;

class Mailbox
{

	/** @var Connection */
	protected $connection;

	/** @var string */
	protected $name;

	public function __construct(Connection $connection, string $name)
	{
		$this->connection = $connection;
		$this->name = $name;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getMails(): Selection
	{
		return $this->query();
	}

	public function query(): Selection
	{
		return new Selection($this->connection, $this);
	}

}
