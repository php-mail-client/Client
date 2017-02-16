<?php
/**
 * This file is part of the imap-mail-list-extractor.
 * Copyright (c) 2017 Grifart spol. s r.o. (https://grifart.cz)
 */

namespace greeny\MailLibrary;

use greeny\MailLibrary\Drivers\IDriver;

class MimePart
{
	/** @var string */
	private $partId;

	/** @var string */
	private $mimeType;

	/** @var string */
	private $name;

	/** @var string */
	private $encoding;

	/** @var IDriver */
	private $driver;

	/** @var string */
	private $mailId;

	public function __construct(IDriver $driver, $mailId, $partId, $mimeType, $name, $encoding)
	{
		$this->partId = $partId;
		$this->mailId = $mailId;
		$this->mimeType = $mimeType;
		$this->name = $name;
		$this->encoding = $encoding;
		$this->driver = $driver;
	}

	/**
	 * @return string
	 */
	public function getPartId()
	{
		return $this->partId;
	}

	/**
	 * @return string
	 */
	public function getMimeType()
	{
		return $this->mimeType;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getEncoding()
	{
		return $this->encoding;
	}

	public function getContent()
	{
		// todo: this automatically decodes content type if supported, support for getting RAW content?
		return $this->driver->getBody($this->mailId, [[
			'id'       => $this->partId,
			'encoding' => $this->encoding,
		]]);
	}

}
