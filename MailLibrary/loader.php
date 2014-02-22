<?php
/**
 * @author Tomáš Blatný
 */

require_once "exceptions.php";

spl_autoload_register(function ($type) {
	static $paths = array(
		'connection' => 'Connection.php',
		'mailbox' => 'Mailbox.php',
		'selection' => 'Selection.php',
		'mail' => 'Mail.php',
		'contactlist' => 'ContactList.php',
		'attachment' => 'Attachment.php',
		'structures\istructure' => 'Structures/IStructure.php',
		'structures\imapstructure' => 'Structures/ImapStructure.php',
		'drivers\idriver' => 'Drivers/IDriver.php',
		'drivers\imapdriver' => 'Drivers/ImapDriver.php',
		'extensions\maillibraryextension' => 'Extensions/MailLibraryExtension.php',
	);

	$type = ltrim(strtolower($type), '\\'); // PHP namespace bug #49143

	if (isset($paths['greeny\\maillibrary\\'.$type])) {
		require_once __DIR__ . '/' . $paths[$type];
	}
});