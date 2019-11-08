<?php

namespace PhpMailClient\Structures;

use PhpMailClient\Attachment;

interface IStructure
{

	public function getBody(): string;

	public function getHtmlBody(): string;

	public function getTextBody(): string;

	/**
	 * @return Attachment[]
	 */
	public function getAttachments(): array;

}
