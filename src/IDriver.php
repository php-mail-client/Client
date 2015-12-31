<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\EmailClient;

use greeny\EmailClient\Exceptions\DriverException;


interface IDriver
{

	/**
	 * Determines if connection is established
	 *
	 * @param bool $ping if connection should be pinged to determine connection status
	 * @return bool
	 */
	function isConnected($ping = FALSE);


	/**
	 * Preforms connection to server
	 *
	 * @throws DriverException on unsuccessful connection
	 */
	function connect();


	/**
	 * Disconnects from server
	 *
	 * @throws DriverException on unsuccessful disconnection
	 */
	function disconnect();


	/**
	 * Flushes changes to server
	 */
	function flush();


	/**
	 * Returns all defined mailboxes
	 *
	 * @return Mailbox[]
	 */
	function getMailboxes();

}
