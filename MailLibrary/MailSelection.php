<?php
/**
 * @package MailLibrary
 * @author Tomáš Blatný
 */

namespace greeny\MailLib;

use \Nette\Object,
	\Iterator,
	\Countable;

/**
 * Description
 */
class MailSelection extends Object implements Iterator, Countable
{
	/** @var array  */
	protected $conditions;

	/** @var array */
	protected $limit = array(
		'count' => 0,
		'offset' => 0,
	);

	/** @var array */
	protected $order = array(
		'type' => NULL,
		'order' => "ASC",
	);

	/** @var array of Mail */
	protected $mails;

	/** @var int */
	protected $iterator;

	/** @var Mailbox */
	protected $mailbox;

	/** @var array */
	protected static $boolConditions = array(
		'ANSWERED',
		'DELETED',
		'FLAGGED',
		'SEEN',
	);

	public function __construct(Mailbox $mailbox)
	{
		$this->mailbox = $mailbox;
	}

	public function where($key, $value)
	{
		if($this->mails) {
			throw new MailException("Cannot add condition to already fetched MailSelection.");
		}

		if(!is_string($value)) {
			throw new MailException("Invalid search condition argument, excepted string");
		}

		$param = substr($key, -3);

		if($param === '[S]') {
			$condition = str_replace('[S]', "\"$value\"", $key);
		} elseif($param === '[D]') {
			$condition = str_replace('[D]', "\"".date("d M Y", strtotime($value))."\"", $key);
		} elseif($value === TRUE) {
			$condition = $key;
		} elseif($value === FALSE) {
			if(in_array($key, self::$boolConditions)) {
				$condition = "UN".$key;
			} else {
				return $this;
			}
		} else {
			throw new MailException("Invalid condition argument.");
		}

		$this->conditions[] = $condition;
		return $this;
	}

	public function limit($limit)
	{
		if($this->mails) {
			throw new MailException("Cannot add limit to already fetched MailSelection.");
		}

		$this->limit['limit'] = $limit;
		return $this;
	}

	public function offset($offset)
	{
		if($this->mails) {
			throw new MailException("Cannot add limit to already fetched MailSelection.");
		}

		$this->limit['offset'] = $offset;
		return $this;
	}

	public function order($key, $order = "ASC")
	{
		if($this->mails) {
			throw new MailException("Cannot reorder already fetched MailSelection.");
		}

		$this->order['type'] = $key;
		$this->order['order'] = in_array($order, array("ASC", "DESC")) ? $order : "ASC";

		return $this;
	}

	public function count()
	{
		return count($this->init()->mails);
	}

	protected function init()
	{
		if($this->order['type'] !== NULL) {
			$mails = imap_sort($this->mailbox->getResource(), $this->buildOrder(), $this->buildReverse(), SE_NOPREFETCH, $this->buildConditions(), 'UTF-8');
		} else {
			$mails = imap_search($this->mailbox->getResource(), $this->buildConditions(), SE_FREE, 'UTF-8');
		}

		foreach($mails as $mail) {
			$this->mails[] = new Mail($this->mailbox->getConnection(), $mail);
		}

		return $this;
	}

	protected function buildConditions()
	{
		return implode(' ', $this->conditions);
	}

	protected function buildOrder()
	{
		return $this->order['type'];
	}

	protected function buildReverse()
	{
		return $this->order['order'] === 'ASC' ? 0 : 1;
	}

	public function get()
	{
		return $this->init()->mails;
	}

	public function getMailById($id) {
		if(isset($this->init()->mails[$id])) {
			return $this->mails[$id];
		} else {
			throw new MailException("Mail with id $id not found.");
		}
	}

	public function rewind() {
		$this->init()->iterator = 0;
	}

	public function next() {
		$this->iterator++;
	}

	public function current() {
		return $this->mails[$this->iterator];
	}

	public function valid() {
		return isset($this->mails[$this->iterator]);
	}

	public function key() {
		return $this->iterator;
	}
}
