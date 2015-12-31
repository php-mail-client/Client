<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\MailLibrary;

use Iterator;
use Countable;

class ContactList implements Iterator, Countable
{
	/** @var Contact[] */
	protected $contacts = [];

	protected $builtContacts = [];

	public function addContact($mailbox = NULL, $host = NULL, $personal = NULL, $adl = NULL)
	{
		$this->contacts[] = new Contact($mailbox,$host,$personal,$adl);
	}

	public function build()
	{
		$return = array();
		foreach($this->contacts as $contact) {
			$return[] = $contact->__toString();
		}
		$this->builtContacts = $return;
	}

	public function getContacts()
	{
		return $this->builtContacts;
	}

	/**
	 * @return array
	 */
	public function getContactsObjects()
	{
		return $this->contacts;
	}

	public function __toString()
	{
		return implode(', ', $this->builtContacts);
	}

	public function current()
	{
		return current($this->builtContacts);
	}

	public function next()
	{
		next($this->builtContacts);
	}

	public function key()
	{
		return key($this->builtContacts);
	}

	public function valid()
	{
		$key = key($this->builtContacts);
		return ($key !== NULL && $key !== FALSE);
	}

	public function rewind()
	{
		reset($this->builtContacts);
	}

	/**
	 * @return int
	 */
	public function count()
	{
		return count($this->builtContacts);
	}
}
