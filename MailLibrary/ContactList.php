<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\MailLibrary;

class ContactList {
	protected $contacts;

	public function addContact($mailbox = NULL, $host = NULL, $personal = NULL, $adl = NULL)
	{
		$this->contacts[] = array(
			'mailbox' => $mailbox,
			'host' => $host,
			'personal' => $personal,
			'adl' => $adl,
		);
	}

	public function getContacts()
	{
		return $this->buildContacts();
	}

	public function __toString()
	{
		return implode(', ', $this->buildContacts());
	}

	protected function buildContacts()
	{
		$return = array();
		foreach($this->contacts as $contact) {
			$address = $contact['personal'] ? "\"" . $contact['personal']. "\" " : "";
			$address .= $contact['adl'] ? $contact['adl'].":" : "";
			$address .= "<".$contact['mailbox']."@".$contact['host'].">";
			$return[] = $address;
		}
		return $return;
	}
}
 