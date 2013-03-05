<?php
/**
 * @package MailLib
 * @author Tomáš Blatný
 */

namespace greeny\MailLib;

use Nette\StaticClassException;

/**
 * Provides functionality to debug MailLib
 */
class MailDebug
{
	const USING = 'using()';

	protected static $data = array(
		'actions' => array(),
	);

	public function __construct()
	{
		throw new StaticClassException();
	}

	public static function init()
	{
		register_shutdown_function(array(__CLASS__, 'echoBar'));
	}

	public static function logAction($action, $time = NULL)
	{
		self::$data['actions'][] = "$action call.".(isset($time) ? " (".$time." ms)" : "");
	}

	public static function echoBar()
	{
		//dump(self::$data);
	}
}
