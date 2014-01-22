<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\MailLibrary\Structures;

use greeny\MailLibrary\Attachment;

interface IStructure {
	/**
	 * @return string
	 */
	function getBody();

	/**
	 * @return string
	 */
	function getHtmlBody();

	/**
	 * @return string
	 */
	function getTextBody();

	/**
	 * @return Attachment[]
	 */
	function getAttachments();
}