<?php

namespace PhpMailClient\Structures;

use PhpMailClient\Attachment;
use PhpMailClient\Drivers\ImapDriver;
use PhpMailClient\Mailbox;

class ImapStructure implements IStructure
{

	const TYPE_TEXT = 0;
	const TYPE_MULTIPART = 1;
	const TYPE_MESSAGE = 2;
	const TYPE_APPLICATION = 3;
	const TYPE_AUDIO = 4;
	const TYPE_IMAGE = 5;
	const TYPE_VIDEO = 6;
	const TYPE_OTHER = 7;

	const ENCODING_7BIT = 0;
	const ENCODING_8BIT = 1;
	const ENCODING_BINARY = 2;
	const ENCODING_BASE64 = 3;
	const ENCODING_QUOTED_PRINTABLE = 4;
	const ENCODING_OTHER = 5;

	protected static $typeTable = [
		self::TYPE_TEXT => 'text',
		self::TYPE_MULTIPART => 'multipart',
		self::TYPE_MESSAGE => 'message',
		self::TYPE_APPLICATION => 'application',
		self::TYPE_AUDIO => 'audio',
		self::TYPE_IMAGE => 'image',
		self::TYPE_VIDEO => 'video',
		self::TYPE_OTHER => 'other',
	];

	/** @var ImapDriver */
	protected $driver;

	/** @var int */
	protected $id;

	/** @var array */
	protected $htmlBodyIds = [];

	/** @var array */
	protected $textBodyIds = [];

	/** @var array */
	protected $attachmentsIds = [];

	/** @var string */
	protected $htmlBody = NULL;

	/** @var string */
	protected $textBody = NULL;

	/** @var Attachment[] */
	protected $attachments = NULL;

	/** @var Mailbox */
	protected $mailbox;

	public function __construct(ImapDriver $driver, $structure, $mailId, Mailbox $mailbox)
	{
		$this->driver = $driver;
		$this->id = $mailId;
		$this->mailbox = $mailbox;
		if (!isset($structure->parts)) {
			$this->addStructurePart($structure, '0');
		} else {
			foreach ((array) $structure->parts as $id => $part) {
				$this->addStructurePart($part, (string) ($id + 1));
			}
		}
	}

	public function getBody(): string
	{
		return count($this->htmlBodyIds) ? $this->getHtmlBody() : $this->getTextBody();
	}

	public function getHtmlBody(): string
	{
		if ($this->htmlBody === NULL) {
			$this->driver->switchMailbox($this->mailbox->getName());
			$this->htmlBody = $this->driver->getBody($this->id, $this->htmlBodyIds);
		}

		return $this->htmlBody;
	}

	public function getTextBody(): string
	{
		if ($this->textBody === NULL) {
			$this->driver->switchMailbox($this->mailbox->getName());
			$this->textBody = $this->driver->getBody($this->id, $this->textBodyIds);
		}

		return $this->textBody;
	}

	/**
	 * @return Attachment[]
	 */
	public function getAttachments(): array
	{
		$this->driver->switchMailbox($this->mailbox->getName());
		if ($this->attachments === NULL) {
			$this->attachments = [];
			foreach ($this->attachmentsIds as $attachmentData) {
				$this->attachments[] = new Attachment($attachmentData['name'], $this->driver->getBody($this->id, [$attachmentData]), $attachmentData['type']);
			}
		}
		return $this->attachments;
	}

	private function addStructurePart($structure, string $partId): void
	{
		$type = $structure->type;
		$encoding = $structure->encoding ?? 'UTF-8';
		$subtype = $structure->ifsubtype ? $structure->subtype : 'PLAIN';

		$parameters = [];
		if ($structure->ifparameters) {
			foreach ($structure->parameters as $parameter) {
				$parameters[strtolower($parameter->attribute)] = $parameter->value;
			}
		}
		if ($structure->ifdparameters) {
			foreach ($structure->dparameters as $parameter) {
				$parameters[strtolower($parameter->attribute)] = $parameter->value;
			}
		}

		if (isset($parameters['filename']) || isset($parameters['name'])) {
			$this->attachmentsIds[] = [
				'id' => $partId,
				'encoding' => $encoding,
				'name' => $parameters['filename'] ?? $parameters['name'],
				'type' => self::$typeTable[$type] . '/' . $subtype,
			];
		} elseif ($type === self::TYPE_TEXT) {
			if ($subtype === 'HTML') {
				$this->htmlBodyIds[] = ['id' => $partId, 'encoding' => $encoding];
			} elseif ($subtype === 'PLAIN') {
				$this->textBodyIds[] = ['id' => $partId, 'encoding' => $encoding];
			}
		}

		if (isset($structure->parts)) {
			foreach ((array)$structure->parts as $id => $part) {
				$this->addStructurePart($part, $partId . '.' . ($id + 1));
			}
		}
	}
}

