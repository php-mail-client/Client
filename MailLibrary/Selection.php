<?php
/**
 * @package MailLibrary
 * @author Tomáš Blatný
 */

namespace greeny\MailLibrary;

/**
 * Represents group of mails, that can be ordered, filtered and paginated
 */
class Selection
{
    /** @var \greeny\MailLibrary\Connection */
    protected $connection;

    /** @var \greeny\MailLibrary\Filter */
    protected $filter;

    /** @var bool */
    protected $locked = FALSE;

    /** @var array of Mails */
    protected $mails = array();


    public function __construct(Connection $connection, Filter $filter = NULL)
    {
        $this->connection = $connection;
        $this->filter = $filter !== NULL ? $filter : new Filter;
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
        return array_keys($this->loadMails()->mails);
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

    /*public function merge(Selection $selection)
    {
        $this->checkLocked();
        return new Selection($this->connection, $this->filter->merge($selection->filter));
    }*/

    protected function loadMails()
    {
        if(!$this->locked) {
            $this->lock()->mails = $this->connection->getDriver()->getMails($this->filter);
        }
        return $this;
    }

    protected function lock()
    {
        $this->locked = FALSE;
        return $this;
    }

    protected function checkLocked()
    {
        if($this->locked) {
            throw new LockedSelectionException("You cannot modify Selection when you have already fetched mails.");
        }
        return $this;
    }
}
