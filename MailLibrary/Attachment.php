<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\MailLibrary;

class Attachment {
	/** @var string */
	protected $name;

	/** @var string */
	protected $content;

	/** @var string */
	protected $type;

	public function __construct($name, $content, $type)
	{
		$this->name = $name;
		$this->content = $content;
		$this->type = $type;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $filename
	 * @return bool
	 */
	public function saveAs($filename)
	{
		return file_put_contents($filename, $this->content) !== FALSE;
	}

	/**
	 * @return string
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}
}
 