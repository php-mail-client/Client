<?php
/**
 * @package MailLibrary
 * @author Tomáš Blatný
 */

namespace greeny\MailLibrary;

use \Exception;

/**
 * Basic mail exception
 */
class MailException extends Exception {}

class InvalidMailboxNameException extends MailException {}

class ConnectionException extends MailException {}

class LockedSelectionException extends MailException {}

class InvalidIdException extends MailException {}

class InvalidDriverException extends MailException {}

class InvalidHeaderKeyException extends MailException {}

class InvalidStructureIndexException extends MailException {}

class DriverException extends MailException {}
