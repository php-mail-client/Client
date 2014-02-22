<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\MailLibrary\Extensions;

use Nette\DI\CompilerExtension;

class MailLibraryExtension extends CompilerExtension {
	public function loadConfiguration()
	{
		$config = $this->getConfig(array(
			'username' => '',
			'password' => '',
			'host' => 'localhost',
			'port' => 993,
			'ssl' => 'true',
		));

		$config = $config['imap'];

		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('connection'))
			->setClass('greeny\\MailLibrary\\Connection');

		$builder->addDefinition($this->prefix('imap'))
			->setClass('greeny\\MailLibrary\\Drivers\\ImapDriver', array(
				$config['username'],
				$config['password'],
				$config['host'],
				$config['port'],
				$config['ssl'],
			));
	}
}
 