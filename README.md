MailLibrary - usage
===================

Initialize
----------
```php
$mails = new MailGetter('username', 'password', 'imap.connection.com', 993, TRUE)
// has parameters $username, $password, $host, $port, $enableSSL
```

You now can access all mails:
-----------------------------
- using foreach:
```php
foreach($mails as $id => $mail)
```

- directly through id:
```php
$mail = $mails[$id];
```

*You can also count number of mails using:*
```php
$count = count($mails);
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
$header = $mail->getHeader('headerTitle'); // also use sanitized name
//$header will contain empty string if header not found.

$header = $mail->getHeader('headerTitle', TRUE); // throws exception when header not found.
```

You can get their content:
```php
$body = $mail->body; //returns HTML body, if not set, returns text body
$textBody = $mail->textBody;
$htmlBody = $mail->htmlBody;
```

Deleting mails
--------------
You can delete mail using MailGetter $mails variable (NOT using $mail variable).
```php
unset($mails[$mailId]);
```

*All changes are only saved to memory and sent to server in destructor. To send it manually, use:*
```php
$mails->flush();
```
