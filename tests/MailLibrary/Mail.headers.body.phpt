<?php
/** @var \greeny\MailLibrary\Connection $connection */
$connection = require "../bootstrap.php";

use Tester\Assert;

$mail = $connection->getMailbox('x')->getMails()->fetchAll()[1];

Assert::equal(array('name' => md5(1), 'id' => 1), $mail->getHeaders());
Assert::equal(md5(1), $mail->name);
Assert::equal(str_repeat('body', 10), $mail->getBody());
Assert::equal(str_repeat('textbody', 10), $mail->getTextBody());
Assert::equal(str_repeat('htmlbody', 10), $mail->getHtmlBody());