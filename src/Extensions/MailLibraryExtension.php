<?php

namespace PhpMailClient\Extensions;

use Nette\DI\CompilerExtension;
use PhpMailClient\Connection;
use PhpMailClient\Drivers\ImapDriver;

class MailLibraryExtension extends CompilerExtension
{

	public function loadConfiguration(): void
	{
		$config = $this->getConfig([
			'imap' => [
				'username' => '',
				'password' => '',
				'host' => 'localhost',
				'port' => 993,
				'ssl' => 'true',
			],
		]);

		$config = $config['imap'];

		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('connection'))
			->setClass(Connection::class);

		$builder->addDefinition($this->prefix('imap'))
			->setClass(ImapDriver::class, [
				$config['username'],
				$config['password'],
				$config['host'],
				$config['port'],
				$config['ssl'],
			]);
	}

}
