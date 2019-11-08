<?php

use PhpMailClient\Connection;
use Tester\Assert;

/** @var Connection $connection */
$connection = require __DIR__ . '/../bootstrap.php';

$connection->connect();

Assert::equal(TRUE, $connection->isConnected());
