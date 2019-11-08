<?php

namespace PhpMailClient\Drivers;

use PhpMailClient\Mailbox;
use PhpMailClient\Structures\IStructure;

interface IDriver
{

	public function connect(): void;

	public function flush(): void;

	public function getMailboxes(): array;

	public function createMailbox(string $name): void;

	public function renameMailbox(string $from, string $to): void;

	public function deleteMailbox(string $name): void;

	public function switchMailbox(string $name): void;

	public function getMailIds(array $filters, int $limit = 0, int $offset = 0, string $orderBy = NULL, string $orderType = 'ASC'): array;

	public function checkFilter(string $key, $value = NULL): void;

	public function getHeaders(int $mailId): array;

	public function getStructure(int $mailId, Mailbox $mailbox): IStructure;

	public function getBody(int $mailId, array $data): string;

	public function getFlags(int $mailId): array;

	public function setFlag(int $mailId, string $flag, bool $value): void;

	public function copyMail(int $mailId, string $toMailbox): void;

	public function moveMail(int $mailId, string $toMailbox): void;

	public function deleteMail(int $mailId): void;

}
