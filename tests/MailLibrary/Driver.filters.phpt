<?php

use PhpMailClient\Connection;
use Tester\Assert;

/** @var Connection $connection */
$connection = require __DIR__ . '/../bootstrap.php';

$driver = $connection->getDriver();

/**
 * Helper class for storing data
 */
class TestFilter {
	public $key;
	public $exception;
	public $value;

	public function __construct($key, $value, $exception)
	{
		$this->key = $key;
		$this->value = $value;
		$this->exception = $exception;
	}
}

$exceptions = array(
	new TestFilter('ABC', NULL, "Invalid filter key 'ABC'."),
	new TestFilter('SUBJECT', NULL, "Invalid value type for filter 'SUBJECT', expected string, got NULL."),
	new TestFilter('SINCE', NULL, "Invalid value type for filter 'SINCE', expected DateTime or timestamp, or textual representation of date, got NULL."),
	new TestFilter('SEEN', NULL, "Invalid value type for filter 'SEEN', expected bool, got NULL."),
	new TestFilter('OLD', TRUE, "Cannot assign value to filter 'OLD'."),
);

foreach($exceptions as $exception) {
	Assert::exception(function()use($driver, $exception){
		$driver->checkFilter($exception->key, $exception->value);
	}, '\\greeny\\MailLibrary\\DriverException', $exception->exception);
}
