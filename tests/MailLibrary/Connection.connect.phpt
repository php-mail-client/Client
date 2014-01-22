<?php
/** @var \greeny\MailLibrary\Connection $connection */
$connection = require "../bootstrap.php";

use Tester\Assert;

$connection->connect();

Assert::equal(TRUE, $connection->isConnected());