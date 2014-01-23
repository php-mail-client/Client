<?php
/**
 * @author Tomáš Blatný
 */

use greeny\MailLibrary\Mail;
use greeny\MailLibrary\DriverException;
use greeny\MailLibrary\Structures\IStructure;
use greeny\MailLibrary\Drivers\IDriver;

class TestDriver implements IDriver {
	protected static $filterTable = array(
		Mail::ANSWERED => '%bANSWERED',
		Mail::BCC => 'BCC "%s"',
		Mail::BEFORE => 'BEFORE "%d"',
		Mail::BODY => 'BODY "%s"',
		Mail::CC => 'CC "%s"',
		Mail::DELETED => '%bDELETED',
		Mail::FLAGGED => '%bFLAGGED',
		Mail::FROM => 'FROM "%s"',
		Mail::KEYWORD => 'KEYWORD "%s"',
		Mail::NEW_MESSAGES => 'NEW',
		Mail::NOT_KEYWORD => 'UNKEYWORD "%s"',
		Mail::OLD_MESSAGES => 'OLD',
		Mail::ON => 'ON "%d"',
		Mail::RECENT => 'RECENT',
		Mail::SEEN => '%bSEEN',
		Mail::SINCE => 'SINCE "%d"',
		Mail::SUBJECT => 'SUBJECT "%s"',
		Mail::TEXT => 'TEXT "%s"',
		Mail::TO => 'TO "%s"',
	);

	protected $mailboxes = array('x');

	public function connect() {}

	public function flush() {}

	public function getMailboxes()
	{
		return $this->mailboxes;
	}

	public function createMailbox($name)
	{
		$this->mailboxes[] = $name;
	}

	public function renameMailbox($from, $to)
	{
		foreach($this->mailboxes as $key => $mailbox) {
			if($mailbox == $from) {
				$this->mailboxes[$key] = $to;
				return;
			}
		}
	}

	public function deleteMailbox($name)
	{
		foreach($this->mailboxes as $key => $mailbox) {
			if($mailbox == $name) {
				unset($this->mailboxes[$key]);
				return;
			}
		}
	}

	public function switchMailbox($name) {}

	public function getMailIds(array $filters)
	{
		if(count($filters)) return array(1);
		else return array(1, 2);
	}

	public function checkFilter($key, $value = NULL)
	{
		if(!in_array($key, array_keys(self::$filterTable))) {
			throw new DriverException("Invalid filter key '$key'.");
		}
		$filtered = self::$filterTable[$key];
		if(strpos($filtered, '%s') !== FALSE) {
			if(!is_string($value)) {
				throw new DriverException("Invalid value type for filter '$key', expected string, got ".gettype($value).".");
			}
		} else if(strpos($filtered, '%d') !== FALSE) {
			if(!($value instanceof DateTime) && !is_int($value) && !strtotime($value)) {
				throw new DriverException("Invalid value type for filter '$key', expected DateTime or timestamp, or textual representation of date, got ".gettype($value).".");
			}
		} else if(strpos($filtered, '%b') !== FALSE) {
			if(!is_bool($value)) {
				throw new DriverException("Invalid value type for filter '$key', expected bool, got ".gettype($value).".");
			}
		} else if($value !== NULL) {
			throw new DriverException("Cannot assign value to filter '$key'.");
		}
	}

	public function getHeaders($mailId)
	{
		return array(
			'name' => md5($mailId),
			'id' => $mailId,
		);
	}

	public function getStructure($mailId, \greeny\MailLibrary\Mailbox $mailbox)
	{
		return new TestStructure();
	}

	public function getBody($mailId, array $partIds)
	{
		return str_repeat($mailId, 10);
	}

	public function getFlags($mailId)
	{

	}

	function setFlag($mailId, $flag, $value)
	{

	}

	function copyMail($mailId, $toMailbox)
	{

	}

	function moveMail($mailId, $toMailbox)
	{

	}

	function deleteMail($mailId)
	{

	}
}

class TestStructure implements IStructure {
	public function getBody()
	{
		return str_repeat('body', 10);
	}

	/**
	 * @return string
	 */
	public function getHtmlBody()
	{
		return str_repeat('htmlbody', 10);
	}

	/**
	 * @return string
	 */
	public function getTextBody()
	{
		return str_repeat('textbody', 10);
	}

	/**
	 * @return \greeny\MailLibrary\Attachment[]
	 */
	public function getAttachments() {}
}