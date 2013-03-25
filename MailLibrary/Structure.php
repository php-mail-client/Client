<?php
/**
 * @package MailLibrary
 * @author Tomáš Blatný
 */

namespace greeny\MailLibrary;

use Nette\Object;
use \stdClass;
use Nette\ArrayHash;
use Nette\Utils\Strings;

/**
 * Represents structure of mail (body, attachments, emails, etc.)
 */
class Structure extends Object
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

    /** @var \greeny\MailLibrary\Connection */
    protected $connection;

    /** @var int */
    protected $id;

    /** @var array of string key => array of string */
    protected $dataIds = array(
        'text' => array(),
        'html' => array(),
        'emails' => array(),
        'attachments' => array(),
        'media' => array(),
    );

    protected $data = array(
        'text' => NULL,
        'html' => NULL,
        'emails' => NULL,
        'attachments' => NULL,
        'media' => NULL,
    );

    /** @var array */
    protected $structure = array();

    public function __construct(Connection $connection, $id)
    {
        $this->connection = $connection;
        $this->id = $id;
        $structure = $this->connection->getDriver()->getMailStructure($id);

        if(isset($structure->parts)) {
            $this->addPart($structure);
        } else {
            $this->addPart($structure, 1);
        }
    }

    public function getSection($section)
    {
        if(isset($this->structure[$section])) {
            return $this->structure[$section];
        } else {
            throw new InvalidStructureIndexException("Structure part with index '$section' not found");
        }
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function getStructure()
    {
        return $this->structure;
    }

    public function getBody()
    {
        return (bool) count($this->dataIds['html']) ? $this->getHtmlBody() : $this->getTextBody();
    }

    public function getHtmlBody()
    {
        if($this->data['html'] !== NULL) {
            return $this->data['html'];
        } else {
            return $this->data['html'] = $this->connection->getDriver()->getBody($this, $this->dataIds['html']);
        }
    }

    public function getTextBody()
    {
        if($this->data['text'] !== NULL) {
            return $this->data['text'];
        } else {
            return $this->data['text'] = $this->connection->getDriver()->getBody($this, $this->dataIds['text']);
        }
    }

    public function getAttachments()
    {
        if($this->data['attachments'] !== NULL) {
            return $this->data['attachments'];
        } else {
            return $this->data['attachments'] = $this->connection->getDriver()->getAttachments($this, $this->dataIds['attachments']);
        }
    }

    public function getAttachedMails()
    {
        if($this->data['mails'] !== NULL) {
            return $this->data['mails'];
        } else {
            return $this->data['mails'] = $this->connection->getDriver()->getAttachedMails($this, $this->dataIds['mails']);
        }
    }

    public function getMedia()
    {
        if($this->data['media'] !== NULL) {
            return $this->data['media'];
        } else {
            return $this->data['media'] = $this->connection->getDriver()->getMedia($this, $this->dataIds['media']);
        }
    }

    protected function addPart($structure, $section = NULL)
    {
        $type = $structure->type;
        $subtype = (bool) $structure->ifsubtype ? $structure->subtype : 'PLAIN';

        if($type === self::TYPE_TEXT) {
            if($subtype === 'HTML') {
                $this->dataIds['html'][] = $section;
            } else {
                $this->dataIds['text'][] = $section;
            }
        } elseif($type === self::TYPE_MESSAGE) {
            $this->dataIds['emails'][] = $section;
        } elseif($type === self::TYPE_MULTIPART) {
            $id = 1;
            foreach($this->parts as $part) {
                $this->addPart($part, "$section.$id");
                $id++;
            }
        } elseif($type === self::TYPE_APPLICATION) {
            $this->dataIds['attachments'][] = $section;
        } else {
            $this->dataIds['media'][] = $section;
        }

        $this->structure[$section] = &$structure;
    }

    protected function parseParams($structure)
    {
        $params = new ArrayHash();
        if($structure->ifdparameters) {
            foreach($structure->dparameters as $parameter) {
                $params->{$this->formatParameterName($parameter->attribute)} = $parameter->value;
            }
        }

        if($structure->ifparameters) {
            foreach($structure->parameters as $parameter) {
                $params->{$this->formatParameterName($parameter->attribute)} = $parameter->value;
            }
        }

        return $params;
    }

    protected function formatParameterName($name)
    {
        return lcfirst(trim(Strings::replace(strtolower($name), "~-.~", function($matches){
            return ucfirst(substr($matches[0], 1));
        })));
    }
}
