<?php
/**
 * @package MailLibrary
 * @author Tomáš Blatný
 */

namespace greeny\MailLib;

use Nette\Object;

/**
 * Description
 */
class Mailbox extends Object
{
	public function __construct($connection, $name) {
		$this->connection = $connection;
		$this->name = $name;
	}
}
