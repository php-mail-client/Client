<?php
/**
 * @package MailLibrary
 * @author Tomáš Blatný
 */

namespace greeny\MailLibrary;

use Nette\FreezableObject;

/**
 * Represents group of mails, that can be ordered, filtered and paginated
 */
class Selection extends FreezableObject
{
    /** @var \greeny\MailLibrary\Connection */
    protected $connection;

    /** @var \greeny\MailLibrary\Filter */
    protected $filter;

    /** @var array of Mails */
    protected $mails = array();

    /** @var string */
    protected $name;


    public function __construct(Connection $connection, $name, Filter $filter = NULL)
    {
        $this->connection = $connection;
        $this->filter = $filter !== NULL ? $filter : new Filter;
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function rename($to)
    {
        return $this->connection->renameMailbox($this->name, $to);
    }

    public function delete()
    {
        $this->connection->deleteMailbox($this->name);
        return NULL;
    }

    public function where($key, $value = NULL)
    {
        $this->checkLocked()->filter->addCondition($key, $value);
    }

    public function order($order, $type = "ASC") {
        $this->checkLocked()->filter->order = array('order' => $order, 'type' => $type);
    }

    public function limit($limit)
    {
        $this->checkLocked()->filter->limit = $limit;
    }

    public function offset($offset)
    {
        $this->checkLocked()->filter->offset = $offset;
    }

    public function getMailIds()
    {
        return array_keys($this->getAllMails());
    }

    public function getAllMails()
    {
        return $this->loadMails()->mails;
    }

    public function getMailById($id)
    {
        if(isset($this->loadMails()->mails[$id])) {
            return $this->mails[$id];
        } else {
            throw new InvalidIdException("Email with id '$id' not found in Selection.");
        }
    }

    public function count()
    {
        return count($this->loadMails()->mails);
    }

    protected function loadMails()
    {
        $this->using();
        if(!$this->isFrozen()) {
            $this->lock()->mails = $this->connection->getDriver()->getMails($this->filter);
        }
        return $this;
    }

    protected function lock()
    {
        $this->freeze();
        return $this;
    }

    protected function checkLocked()
    {
        $this->updating();
        $this->using();
        return $this;
    }

    protected function using()
    {
        $this->connection->using($this->name);
        return $this;
    }
}
