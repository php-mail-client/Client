<?php

namespace PhpMailClient;

class Attachment
{

	/** @var string */
	private $name;

	/** @var string */
	private $content;

	/** @var string */
	private $type;

	public function __construct(string $name, string $content, string $type)
	{
		$this->name = $name;
		$this->content = $content;
		$this->type = $type;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function saveAs($filename): bool
	{
		return file_put_contents($filename, $this->content) !== FALSE;
	}

	public function getContent(): string
	{
		return $this->content;
	}

	public function getType(): string
	{
		return $this->type;
	}
}
