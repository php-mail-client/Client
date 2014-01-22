<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\MailLibrary\Structures;

use greeny\MailLibrary\Attachment;
use greeny\MailLibrary\Drivers\ImapDriver;
use greeny\MailLibrary\NotImplementedException;

class ImapStructure implements IStructure {
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

	/** @var \greeny\MailLibrary\Drivers\ImapDriver */
	protected $driver;

	/** @var int */
	protected $id;

	/** @var array */
	protected $htmlBodyIds = array();

	/** @var array */
	protected $textBodyIds = array();

	/** @var array */
	protected $attachmentsIds = array();

	/** @var string */
	protected $htmlBody = NULL;

	/** @var string */
	protected $textBody = NULL;

	/** @var Attachment[] */
	protected $attachments = NULL;

	/**
	 * @param ImapDriver $driver
	 * @param object     $structure
	 * @param int        $mailId
	 */
	public function __construct(ImapDriver $driver, $structure, $mailId)
	{
		$this->driver = $driver;
		$this->id = $mailId;
		if(!isset($structure->parts)) {
			$this->addStructurePart($structure, '0');
		} else {
			foreach((array)$structure->parts as $id => $part) {
				$this->addStructurePart($part, $id+1);
			}
		}
	}

	/**
	 * @return string
	 */
	public function getBody()
	{
		return count($this->htmlBodyIds) ? $this->getHtmlBody() : $this->getTextBody();
	}

	/**
	 * @return string
	 */
	public function getHtmlBody()
	{
		return $this->htmlBody === NULL ? $this->htmlBody = $this->driver->getBody($this->id, $this->htmlBodyIds) : $this->textBody;
	}

	/**
	 * @return string
	 */
	public function getTextBody()
	{
		return $this->htmlBody === NULL ? $this->htmlBody = $this->driver->getBody($this->id, $this->textBodyIds) : $this->htmlBody;
	}

	/**
	 * @return Attachment[]
	 * @throws \greeny\MailLibrary\NotImplementedException
	 */
	public function getAttachments()
	{
		throw new NotImplementedException();
	}

	protected function addStructurePart($structure, $partId)
	{
		$type = $structure->type;
		$encoding = isset($structure->encoding) ? $structure->encoding : 'UTF-8';
		$subtype = $structure->ifsubtype ? $structure->subtype : 'PLAIN';

		$parameters = array();
		if($structure->ifparameters) {
			foreach($structure->parameters as $parameter) {
				$parameters[strtolower($parameter->attribute)] = $parameter->value;
			}
		}
		if($structure->ifdparameters) {
			foreach($structure->dparameters as $parameter) {
				$parameters[strtolower($parameter->attribute)] = $parameter->value;
			}
		}

		if(isset($parameters['filename']) || isset($parameters['name'])) {
			$this->attachmentsIds[] = array('id' => $partId, 'encoding' => $encoding, 'name' => isset($parameters['filename']) ? $parameters['filename'] : $parameters['name']);
		} else if($type === self::TYPE_TEXT) {
			if($subtype === 'HTML') {
				$this->htmlBodyIds[] = array('id' => $partId, 'encoding' => $encoding);
			} else if($subtype === 'PLAIN') {
				$this->textBodyIds[] = array('id' => $partId, 'encoding' => $encoding);;
			}
		}

		if(isset($structure->parts)) {
			foreach((array)$structure->parts as $id => $part) {
				$this->addStructurePart($part, $partId.'.'.($id+1));
			}
		}
	}
}
 