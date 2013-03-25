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
    const FLAG_SEEN = "\\SEEN";
    const FLAG_FLAGGED = "\\FLAGGED";
    const FLAG_ANSWERED = "\\ANSWERED";
    const FLAG_DELETED = "\\DELETED";
    const FLAG_DRAFT = "\\DRAFT";

    /** @var \greeny\MailLibrary\Connection */
    protected $connection;

    /** @var int */
    protected $id;

    /** @var string */
    protected $mailbox;

    /** @var array of string key => string value */
    protected $headers = array();

    /** @var bool */
    protected $initializedHeaders = FALSE;

    /** @var \greeny\MailLibrary\Structure */
    protected $structure;

    protected $initializedStructure = FALSE;


    public function __construct(Connection $connection, $id, $mailbox)
    {
        $this->connection = $connection;
        $this->id = $id;
        $this->mailbox = $mailbox;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getMailboxName()
    {
        return $this->mailbox;
    }

    public function getHeader($key, $default = NULL, $need = FALSE)
    {
        $key = $this->formatHeaderName($key);
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
        if(isset($this->headers[$key])) {
            return $this->headers[$key];
        } elseif($need === TRUE) {
            throw new InvalidHeaderKeyException("Formatted header '$key' not found.");
        } else {
            return $default;
        }
    }

    public function copyTo($mailbox)
    {
        $this->connection->copyMail($this->mailbox, $this->id, $mailbox);
        return $this;
    }

    public function moveTo($mailbox)
    {
        $this->connection->moveMail($this->mailbox, $this->id, $mailbox);
        return NULL;
    }

    public function delete()
    {
        $this->connection->deleteMail($this->mailbox, $this->id);
        return NULL;
    }

    public function setFlags($flags, $bool = TRUE)
    {
        if(!is_array($flags)) {
            $flags = array($flags);
        }
        $this->connection->setMailFlags($this->mailbox, $this->id, $flags, $bool);
    }

    protected function initializeHeaders()
    {
        if(!$this->initializedHeaders) {
            $this->setHeaders($this->connection->getDriver()->getMailHeaders($this->id))->initializedHeaders = TRUE;
        }
    }

    protected function setHeaders(array $headers)
    {
        foreach($headers as $key => $header) {
            $this->headers[$this->formatHeaderName($key)] = $header;
        }
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
