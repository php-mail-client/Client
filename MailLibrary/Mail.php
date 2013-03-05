<?php
/**
 * @package MailLibrary
 * @author Tomáš Blatný
 */

namespace greeny\MailLib;

use Nette\Object,
	Nette\Utils\Strings;

/**
 * Represents one mail.
 */
class Mail extends Object
{
	const ORDER_DATE = SORTDATE;
	const ORDER_ARRIVAL = SORTARRIVAL;
	const ORDER_FROM = SORTFROM;
	const ORDER_SUBJECT = SORTSUBJECT;
	const ORDER_TO = SORTTO;
	const ORDER_CC = SORTCC;
	const ORDER_SIZE = SORTSIZE;

	const FILTER_ANSWERED = "ANSWERED";
	const FILTER_BCC = "BCC [S]";
	const FILTER_BEFORE = "BEFORE [D]";
	const FILTER_BODY = "BODY [S]";
	const FILTER_CC = "CC [S]";
	const FILTER_DELETED = "DELETED";
	const FILTER_FLAGGED = "FLAGGED";
	const FILTER_FROM = "FROM [S]";
	const FILTER_KEYWORD = "KEYWORD [S]";
	const FILTER_NEW = "NEW";
	const FILTER_OLD = "OLD";
	const FILTER_RECENT = "RECENT";
	const FILTER_SEEN = "SEEN";
	const FILTER_SINCE = "SINCE [D]";
	const FILTER_SUBJECT = "SUBJECT [S]";
	const FILTER_TEXT = "TEXT [S]";
	const FILTER_TO = "TO [S]";
	const FILTER_NOTKEYWORD = "UNKEYWORD [S]";

	/** @var int */
	protected $id;

	/** @var Connection */
	protected $connection;

	/** @var MailStructure */
	protected $structure;

	/** @var array */
	protected $rawData = array(
		'headers' => NULL,
	);

	/** @var array */
	protected $data = array(
		'headers' => array(),
		'formattedHeaders' => array(),
	);

	/**
	 * Mail constructor
	 *
	 * @param Connection    $connection  connection to mail server
	 * @param int           $id          mail id
	 */
	public function __construct(Connection $connection, $id)
	{
		$this->id = $id;
		$this->connection = $connection;
	}

	/**
	 * Initializes headers for this mail. Internal function, do not call directly.
	 *
	 * @return Mail Provides fluent interface.
	 */
	protected function initializeHeaders()
	{
		if(!$this->rawData['headers']) {
			$this->rawData['headers'] = $headers = imap_fetchheader($this->connection->getConnection(), $this->id);
			$h = Strings::split($headers, "#\r?\n#");
			for($i = count($h) - 1; $i >= 0; $i--) {
				if(substr($h[$i], 0, 1) === ' ') {
					$h[$i-1] .= $h[$i];
					unset($h[$i]);
				}
			}

			foreach($h as $header) {
				$data = explode(":", $header);
				$key = $data[0];
				$formattedKey = lcfirst(Strings::replace($key, "~-.~", function($matches){
					return ucfirst(substr($matches[0], 1));
				}));
				unset($data[0]);
				if($formattedKey === 'subject') {
					$value = imap_mime_header_decode(trim(implode(':', $data)));
					if(isset($value[0]->text)) {
						$value = $value[0]->text;
					}
				} else {
					$value = imap_utf8(trim(implode(':', $data)));
				}
				$this->data['headers'][$key] = $value;
				$this->data['formattedHeaders'][$formattedKey] = $value;
			}
		}

		return $this;
	}

	/**
	 * Getter for mail id.
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Initializes structure for this mail. Internal function, do not call directly.
	 *
	 * @return Mail Provides fluent interface.
	 */
	protected function initializeStructure()
	{
		if(!$this->structure) {
			$this->structure = new MailStructure($this->connection->getConnection(), $this->id);
		}
		return $this;
	}

	/**
	 * Body getter
	 *
	 * @return string
	 */
	public function getBody()
	{
		return ($body = $this->getHtmlBody()) === '' ? $this->getTextBody() : $body;
	}

	/**
	 * Text body getter
	 *
	 * @return string
	 */
	public function getTextBody()
	{
		return $this->initializeStructure()->getStructure()->getText();
	}

	/**
	 * HTML body getter
	 *
	 * @return string
	 */
	public function getHtmlBody()
	{
		return $this->initializeStructure()->getStructure()->getHtml();
	}

	/**
	 * MailStructure getter
	 *
	 * @return MailStructure
	 */
	public function getStructure()
	{
		return $this->initializeStructure()->structure;
	}

	/**
	 * Getter for headers
	 *
	 * @param string    $name   Header name
	 * @param bool      $need   Throw exception if not found?
	 * @return string
	 * @throws MailException When $need === TRUE and header not found.
	 */
	public function getHeader($name, $need = FALSE)
	{
		$this->initializeHeaders();
		if(isset($this->data['headers'][$name])) {
			return $this->data['headers'][$name];
		} else {
			if($need) {
				throw new MailException("Header '$name' not found.");
			} else {
				return "";
			}
		}
	}

	/**
	 * Getter for any header.
	 *
	 * @param string    $name   Header name
	 * @return mixed
	 */
	public function &__get($name)
	{
		$this->initializeHeaders();
		if(isset($this->data['formattedHeaders'][$name])) {
			return $this->data['formattedHeaders'][$name];
		} else {
			return parent::__get($name);
		}
	}
}
