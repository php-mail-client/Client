<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\EmailClient\Exceptions;

use Exception;


class DriverException extends EmailClientException
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
