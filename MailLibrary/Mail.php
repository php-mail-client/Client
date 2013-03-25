<?php
/**
 * @package MailLibrary
 * @author Tomáš Blatný
 */

namespace greeny\MailLibrary;

use Nette\Object;
use Nette\Utils\Strings;

/**
 * Represents one mail.
 */
class Mail extends Object
{
    /** @var \greeny\MailLibrary\Connection */
    protected $connection;

    /** @var int */
    protected $id;

    /** @var array of string key => string value */
    protected $headers = array();

    /** @var bool */
    protected $initializedHeaders = FALSE;

    /** @var \greeny\MailLibrary\Structure */
    protected $structure;

    protected $initializedStructure = FALSE;


    public function __construct(Connection $connection, $id)
    {
        $this->connection = $connection;
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getHeader($key, $default = NULL, $need = FALSE)
    {
        if(isset($this->headers[$key])) {
            return $this->headers[$key];
        } elseif($need === TRUE) {
            throw new InvalidHeaderKeyException("Header '$key' not found.");
        } else {
            return $default;
        }
    }

    public function getFormattedHeader($key, $default = NULL, $need = FALSE)
    {
        $key = $this->formatHeaderName($key);
        if(isset($this->headers[$key])) {
            return $this->headers[$key];
        } elseif($need === TRUE) {
            throw new InvalidHeaderKeyException("Formatted header '$key' not found.");
        } else {
            return $default;
        }
    }

    protected function initializeHeaders()
    {
        if(!$this->initializedHeaders) {
            $this->setHeaders($this->connection->getDriver()->getMailHeaders($this->id))->initializedHeaders = TRUE;
        }
    }

    protected function setHeaders(array $headers)
    {
        $this->headers = $headers;
        return $this;
    }

    protected function formatHeaderName($header)
    {
        return lcfirst(trim(Strings::replace($header, "~-.~", function($matches){
            return ucfirst(substr($matches[0], 1));
        })));
    }

    protected function initializeStructure()
    {
        if(!$this->initializedStucure) {
            $this->structure = new Structure($this->connection, $this->id);
        }
    }
}
