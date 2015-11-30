<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\MailLibrary;

use greeny\MailLibrary\Exceptions\DriverException;


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

}
