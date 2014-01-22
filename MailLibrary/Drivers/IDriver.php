<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\MailLibrary\Drivers;

use greeny\MailLibrary\DriverException;
use greeny\MailLibrary\Structures\IStructure;

interface IDriver {
	/**
	 * Connects to server
	 * @throws DriverException if connecting fails
	 */
	function connect();

	/**
	 * Flushes changes to server
	 * @throws DriverException if flushing fails
	 */
	function flush();

	/**
	 * Gets all mailboxes
	 * @return array of string
	 * @throws DriverException
	 */
	function getMailboxes();

	/**
	 * Creates new mailbox
	 * @param string $name
	 * @throws DriverException
	 */
	function createMailbox($name);

	/**
	 * Renames mailbox
	 * @param string $from
	 * @param string $to
	 * @throws DriverException
	 */
	function renameMailbox($from, $to);

	/**
	 * Deletes mailbox
	 * @param string $name
	 * @throws DriverException
	 */
	function deleteMailbox($name);

	/**
	 * Switches current mailbox
	 * @param string $name
	 * @throws DriverException
	 */
	function switchMailbox($name);

	/**
	 * Finds UIDs of mails by filter
	 * @param array $filters
	 * @throws DriverException
	 * @return array of UIDs
	 */
	function getMailIds(array $filters);

	/**
	 * Checks if filter is applicable for this driver
	 * @param string $key
	 * @param mixed  $value
	 * @throws DriverException
	 */
	function checkFilter($key, $value = NULL);

	/**
	 * Gets mail headers
	 * @param int $mailId
	 * @return array of name => value
	 */
	function getHeaders($mailId);

	/**
	 * Creates structure for mail
	 * @param int $mailId
	 * @return IStructure
	 */
	function getStructure($mailId);

	/**
	 * @param int   $mailId
	 * @param array $partIds
	 * @return string
	 */
	function getBody($mailId, array $partIds);
} 