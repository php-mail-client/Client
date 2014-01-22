<?php
/** @var \greeny\MailLibrary\Connection $connection */
$connection = require "../bootstrap.php";

use Tester\Assert;
use greeny\MailLibrary\Mailbox;

$connection->deleteMailbox('x');
Assert::equal(array(), $connection->getMailboxes());

$mailbox = new Mailbox($connection, 'x');
$created = $connection->createMailbox('x');
Assert::equal($mailbox, $created);
Assert::equal(array('x' => $mailbox), $connection->getMailboxes());

$mailbox = new Mailbox($connection, 'y');
$renamed = $connection->renameMailbox('x', 'y');
Assert::equal($mailbox, $renamed);
Assert::equal(array('y' => $mailbox), $connection->getMailboxes());

$switched = $connection->switchMailbox('y');
Assert::equal($mailbox, $switched);

$connection->deleteMailbox('y');
Assert::equal(array(), $connection->getMailboxes());