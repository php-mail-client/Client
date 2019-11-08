<?php

namespace PhpMailClient;

use ArrayIterator;
use Countable;
use IteratorAggregate;

class ContactList implements IteratorAggregate, Countable
{

	/** @var Contact[] */
	protected $contacts;

	protected $builtContacts;

	public function addContact(?string $mailbox = NULL, ?string $host = NULL, ?string $personal = NULL, ?string $adl = NULL)
	{
		$this->contacts[] = new Contact($mailbox, $host, $personal, $adl);
	}

	public function build(): void
	{
		$return = [];
		foreach ($this->contacts as $contact) {
			$return[] = (string) $contact;
		}
		$this->builtContacts = $return;
	}

	public function getContacts(): array
	{
		return $this->builtContacts;
	}

	/**
	 * @return Contact[]
	 */
	public function getContactsObjects(): array
	{
		return $this->contacts;
	}

	public function __toString(): string
	{
		return implode(', ', $this->builtContacts);
	}

	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->builtContacts);
	}

	public function count(): int
	{
		return count($this->builtContacts);
	}

}
