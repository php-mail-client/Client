MailLibrary v. 1.1.0
====================

Installation
------------
- download plugin and extract it to libs directory
- or use composer (greeny/maillibrary)

Initialize
----------
```php
use greeny\MailLib\Connection;

$connection = new Connection('username', 'password', 'imap.connection.com', 993, TRUE)
// has parameters $username, $password, $host, $port, $enableSSL
```

Getting mailboxes:
------------------
$mailboxes = $connection->getMailboxes();
foreach($mailboxes as $mailbox) {
$name = $mailbox->name;
}

You now also access all mails:
-----------------------------
- using foreach:

```php
foreach($mailbox as $id => $mail)
```

Filtering mails
---------------

```php
$mailbox->where($filterType, $value)
```

Filter types are accessible via greeny\MailLib\Mail class:
```php
$mailbox->where(Mail::FILTER_FROM, "mom@example.com"); // all mails from mom
```

All filters are in Mail class.

You can use fluent interface:

```php
$mailbox->where(Mail::FILTER_SUBJECT, "Hello world!")->where(Mail::FILTER_BODY, "Hello Nette!");
```

Operations with emails:
-----------------------
Now, when you have `$mail` variable, you can get properties:
```php
$subject = $mail->subject;
$to = $mail->to;
$customHeader = $mail->customHeader;
$id = $mail->id; // the $id can be used to reference this email in $mails variable, see Deleting mails
```

*Mail header names are automatically sanitized - e.g. `X-Recieved-from` will become `xRecievedFrom`, etc.*

- Another way to get headers:

```php
$header = $mail->getHeader('Header-Title'); // use not-sanitized name, normally, you would write $mail->headerTitle
//$header will contain empty string if header not found.

$header = $mail->getHeader('Header-Title', TRUE); // throws exception when header not found.
```

You can get their content:
```php
$body = $mail->body; //returns HTML body, if not set, returns text body
$textBody = $mail->textBody;
$htmlBody = $mail->htmlBody;
```
