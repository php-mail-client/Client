<?php

use PhpMailClient\Connection;
use PhpMailClient\Mail;
use Tester\Assert;

/** @var Connection $connection */
$connection = require __DIR__ . '/../bootstrap.php';

$mailbox = $connection->getMailbox('x');

Assert::equal(array(1 => new Mail($connection, $mailbox, 1), 2 => new Mail($connection, $mailbox, 2)), $mailbox->getMails()->fetchAll());
Assert::equal(array(1 => new Mail($connection, $mailbox, 1)), $mailbox->getMails()->where(Mail::FROM, 'mom')->fetchAll());
Assert::equal(new Mail($connection, $mailbox, 1), $mailbox->getMails()->where(Mail::FROM, 'mom')[1]);
