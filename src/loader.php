<?php

use PhpMailClient\Attachment;
use PhpMailClient\Connection;
use PhpMailClient\Contact;
use PhpMailClient\ContactList;
use PhpMailClient\Drivers\IDriver;
use PhpMailClient\Drivers\ImapDriver;
use PhpMailClient\Extensions\MailLibraryExtension;
use PhpMailClient\Mail;
use PhpMailClient\Mailbox;
use PhpMailClient\Selection;
use PhpMailClient\Structures\ImapStructure;
use PhpMailClient\Structures\IStructure;

require_once 'exceptions.php';
class_alias(Connection::class, '\greeny\MailLibrary\Connection');
class_alias(Mailbox::class, '\greeny\MailLibrary\Mailbox');
class_alias(Selection::class, '\greeny\MailLibrary\Selection');
class_alias(Mail::class, '\greeny\MailLibrary\Mail');
class_alias(ContactList::class, '\greeny\MailLibrary\ContactList');
class_alias(Contact::class, '\greeny\MailLibrary\Contact');
class_alias(Attachment::class, '\greeny\MailLibrary\Attachment');
class_alias(IStructure::class, '\greeny\MailLibrary\Structures\IStructure');
class_alias(ImapStructure::class, '\greeny\MailLibrary\Structures\ImapStructure');
class_alias(IDriver::class, '\greeny\MailLibrary\Drivers\IDriver');
class_alias(ImapDriver::class, '\greeny\MailLibrary\Drivers\ImapDriver');
class_alias(MailLibraryExtension::class, '\greeny\MailLibrary\Extensions\MailLibraryExtension');


spl_autoload_register(function ($type) {
	static $paths = [
		'greeny\maillibrary\connection' => 'Connection.php',
		'greeny\maillibrary\mailbox' => 'Mailbox.php',
		'greeny\maillibrary\selection' => 'Selection.php',
		'greeny\maillibrary\mail' => 'Mail.php',
		'greeny\maillibrary\contactlist' => 'ContactList.php',
		'greeny\maillibrary\contact' => 'Contact.php',
		'greeny\maillibrary\attachment' => 'Attachment.php',
		'greeny\maillibrary\structures\istructure' => 'Structures/IStructure.php',
		'greeny\maillibrary\structures\imapstructure' => 'Structures/ImapStructure.php',
		'greeny\maillibrary\drivers\idriver' => 'Drivers/IDriver.php',
		'greeny\maillibrary\drivers\imapdriver' => 'Drivers/ImapDriver.php',
		'greeny\maillibrary\extensions\maillibraryextension' => 'Extensions/MailLibraryExtension.php',
	];

	$type = ltrim(strtolower($type), '\\'); // PHP namespace bug #49143

	if (isset($paths[$type])) {
		require_once __DIR__ . '/' . $paths[$type];
	}
});
