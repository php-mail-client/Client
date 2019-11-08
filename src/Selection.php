<?php

namespace PhpMailClient;

use ArrayIterator;
use Countable;
use IteratorAggregate;

class Selection implements Countable, IteratorAggregate
{

	/** @var Connection */
	protected $connection;

	/** @var Mailbox */
	protected $mailbox;

	/** @var array */
	protected $mails = NULL;

	/** @var array */
	protected $mailIndexes = NULL;

	/** @var array */
	protected $filters = [];

	/** @var int */
	protected $limit = 0;

	/** @var int */
	protected $offset = 0;

	/** @var int */
	protected $orderBy = Mail::ORDER_DATE;

	/** @var string */
	protected $orderType = 'ASC';

	public function __construct(Connection $connection, Mailbox $mailbox)
	{
		$this->connection = $connection;
		$this->mailbox = $mailbox;
	}

	public function where(string $key, $value = NULL): Selection
	{
		$this->connection->getDriver()->checkFilter($key, $value);
		$this->filters[] = ['key' => $key, 'value' => $value];
		return $this;
	}

	public function limit(int $limit): Selection
	{
		if ($limit < 0) {
			throw new InvalidFilterValueException(sprintf('Limit must be bigger or equal to 0, %d given.', $limit));
		}
		$this->limit = $limit;
		return $this;
	}

	public function offset(int $offset): Selection
	{
		if ($offset < 0) {
			throw new InvalidFilterValueException(sprintf('Offset must be bigger or equal to 0, %d given.', $offset));
		}
		$this->offset = $offset;
		return $this;
	}

	public function page(int $page, int $itemsPerPage): Selection
	{
		if ($page <= 0) {
			throw new InvalidFilterValueException(sprintf('Page must be at least 1, %d given.', $page));
		}

		if ($itemsPerPage <= 0) {
			throw new InvalidFilterValueException(sprintf('Items per page must be at least 1, %d given.', $itemsPerPage));
		}

		$this->offset(($page - 1) * $itemsPerPage);
		$this->limit($itemsPerPage);
		return $this;
	}

	public function order(string $by, string $type = 'ASC'): Selection
	{
		$type = strtoupper($type);
		if (!in_array($type, ['ASC', 'DESC'])) {
			throw new InvalidFilterValueException(sprintf('Sort type must be ASC or DESC, %s given.', $type));
		}

		$this->orderBy = $by;
		$this->orderType = $type;
		return $this;
	}

	public function countMails(): int
	{
		return count($this->fetchAll());
	}

	/**
	 * @return Mail[]
	 */
	public function fetchAll(): array
	{
		if ($this->mails !== NULL) {
			$this->fetchMails();
		}
		return $this->mails;
	}

	protected function fetchMails(): void
	{
		$this->connection->getDriver()->switchMailbox($this->mailbox->getName());
		$ids = $this->connection->getDriver()->getMailIds($this->filters, $this->limit, $this->offset, $this->orderBy, $this->orderType);
		$this->mails = [];
		foreach ($ids as $id) {
			$this->mails[$id] = new Mail($this->connection, $this->mailbox, $id);
		}
	}

	public function count(): int
	{
		return $this->countMails();
	}

	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->mails);
	}

}
