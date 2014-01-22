<?php
/**
 * @author Tomáš Blatný
 */

require_once __DIR__."/../vendor/autoload.php";

require_once "MailLibrary/TestDriver.php";

Tester\Environment::setup();
date_default_timezone_set('Europe/Prague');

return new \greeny\MailLibrary\Connection(new TestDriver());