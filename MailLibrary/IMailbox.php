<?php
/**
 * @package MailLibrary
 * @author Tomáš Blatný
 */

namespace greeny\MailLib;

/**
 * Represents mailbox
 */
interface IMailbox
{
	/**
	 * Returns mailbox name
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Counts number of mails
	 *
	 * @return int
	 */
	public function countMails();

	/**
	 * Returns all mails
	 *
	 * @return array of Mail
	 */
	public function getAllMails();
}
