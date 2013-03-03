<?php
/**
 * @package MailLibrary
 * @author Tomáš Blatný
 */

namespace greeny\MailLib;

use Nette\ArrayHash,
	Nette\Utils\Strings,
	Nette\Object;

/**
 * Represents mail structure
 */
class MailStructure extends Object
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

	/** @var string mail text body */
	protected $text = '';

	/** @var string mail html body */
	protected $html = '';

	/** @var array of string section pointers */
	protected $attachedMails = array();

	/** @var array of string section pointers */
	protected $attachments = array();

	/** @var array of string section pointers */
	protected $media = array();

	/** @var object raw mail structure */
	protected $structure;

	/** @var resource connection to mail server */
	protected $connection;

	/** @var int */
	protected $id;

	/**
	 * Initializes structure
	 *
	 * @param resource  $connection connection to mail server
	 * @param int       $id         mail id
	 */
	public function __construct($connection, $id)
	{
		$this->connection = $connection;
		$this->id = $id;
		$this->structure = $structure = imap_fetchstructure($this->connection, $this->id);
		$this->addPart($structure);
	}

	/**
	 * Getter for text body
	 *
	 * @return string
	 */
	public function getText()
	{
		return $this->text;
	}

	/**
	 * Getter for HTML body
	 *
	 * @return string
	 */
	public function getHtml()
	{
		return $this->html;
	}

	/**
	 * Adds $structure subpart to this mail.
	 *
	 * @param object        $structure  The structure to add
	 * @param string|NULL   $section    Actual section number
	 * @return MailStructure Provides fluent interface.
	 */
	protected function addPart($structure, $section = NULL)
	{
		$params = $this->parseParams($structure);

		$type = $structure->type;
		$subtype = ($structure->ifsubtype ? $structure->subtype : 'PLAIN');

		if($structure->type === self::TYPE_TEXT) {
			if($section === NULL) {
				$part = $this->decodePart(imap_body($this->connection, $this->id), $structure->encoding);
			} else {
				$part = $this->decodePart(imap_fetchbody($this->connection, $this->id, $section), $structure->encoding);
			}

			if(isset($params->charset)) {
				$part = CharsetConvertor::convert($part, $params->charset);
			}

			if($subtype === 'HTML') {
				$this->html .= "\n" . $part;
			} else {
				$this->text .= "\n" . $part;
			}
		} elseif($type === self::TYPE_MULTIPART) {
			foreach($structure->parts as $key => $part) {
				if($section !== NULL) {
					$section .= '.';
				}
				$this->addPart($part, $section.($key + 1));
			}
		} elseif($type === self::TYPE_MESSAGE) {
			$this->attachedMails[] = $section;
		} elseif($type === self::TYPE_APPLICATION) {
			$this->attachments[] = $section;
		} else {
			$this->media[] = $section;
		}

		return $this;
	}

	/**
	 * Parses parameters of structure
	 *
	 * @param   object  $structure  The structure to parse
	 * @return \Nette\ArrayHash parameters
	 */
	protected function parseParams($structure)
	{
		$params = new ArrayHash();
		if($structure->ifdparameters) {
			foreach($structure->dparameters as $parameter) {
				$params->{$this->sanitizeParamName($parameter->attribute)} = $parameter->value;
			}
		}

		if($structure->ifparameters) {
			foreach($structure->parameters as $parameter) {
				$params->{$this->sanitizeParamName($parameter->attribute)} = $parameter->value;
			}
		}

		return $params;
	}

	/**
	 * Sanitizes parameter name
	 *
	 * @param string $name Parameter name
	 * @return string sanitized name
	 */
	protected function sanitizeParamName($name)
	{
		return lcfirst(trim(Strings::replace(strtolower($name), "~-.~", function($matches){
			return ucfirst(substr($matches[0], 1));
		})));
	}

	/**
	 * Decodes part of structure
	 *
	 * @param string    $string     Text to decode
	 * @param int       $encoding   Encoding
	 * @return string decoded string
	 */
	protected function decodePart($string, $encoding)
	{
		if($encoding === self::ENCODING_BASE64) {
			$string = base64_decode($string);
		} elseif($encoding === self::ENCODING_QUOTED_PRINTABLE) {
			$string = quoted_printable_decode($string);
		}

		return $string;
	}
}
