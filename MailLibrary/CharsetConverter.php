<?php
/**
 * @package MailLibrary
 * @author Tomáš Blatný
 */

namespace greeny\MailLibrary;

use Nette\Object;
use Nette\StaticClassException;

/**
 * Class to convert from one charset to another, provides case-insensitive access.
 */
class CharsetConverter extends Object
{
    /** @var array */
    protected static $aliasList = array();

    /** @var bool */
    protected static $initialized = FALSE;

    /**
     * Static class
     */
    public function __construct()
    {
        throw new StaticClassException("Cannot initialize static class '".__CLASS__."'.");
    }

    /**
     * Initializes self. Internal function, do not call it directly.
     */
    protected static function init()
    {
        if(!self::$initialized) {
            $enc = mb_list_encodings();
            foreach($enc as $e) {
                self::$aliasList[strtolower($e)] = $e;
            }
            self::$initialized = TRUE;
        }
    }

    /**
     * @param string $string    string to convert
     * @param string $from      from which encoding
     * @param string $to        to which encoding
     * @return string   converted string
     */
    public static function convert($string, $from = NULL, $to = 'utf-8')
    {
        self::init();

        $from = strtolower($from);
        $to = strtolower($to);

        if(isset(self::$aliasList[$from])) {
            $from = self::$aliasList[$from];
        }

        if(isset(self::$aliasList[$to])) {
            $to = self::$aliasList[$to];
        }

        return mb_convert_encoding($string, $to, $from);
    }
}
