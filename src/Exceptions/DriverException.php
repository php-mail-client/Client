<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\MailLibrary\Exceptions;

use Exception;


class DriverException extends MailLibraryException
{

	public static function create($message, $imapError, $code = 0, Exception $previous = NULL)
	{
		if ($imapError) {
			$message .= ': ' . $imapError;
		} else {
			$message .= '.';
		}

		return new self($message, $code, $previous);
	}

}
