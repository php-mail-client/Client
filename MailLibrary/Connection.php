<?php
/**
 * @package MailLibrary
 * @author Tomáš Blatný
 */

namespace greeny\MailLibrary;

use Nette\Object;

use greeny\MailLibrary\Drivers\IDriver;

/**
 * Represents connection to mail server.
 */
class Connection extends Object
{
    /** @var \greeny\MailLibrary\Drivers\IDriver */
    protected $driver = NULL;

    /** @var string */
    protected $serverName = NULL;

    /** @var array of \greeny\MailLibrary\Selection */
    protected $mailboxes = array();

    /** @var bool */
    protected $connected = FALSE;

    /** @var bool */
    protected $initialized = FALSE;

    /** @var string */
    protected $usedMailbox = NULL;


    public function __construct(IDriver $driver = NULL)
    {
        $this->driver = $driver;
    }

    public function getDriver()
    {
        return $this->driver;
    }

    public function getServerName()
    {
        return $this->serverName;
    }

    public function getMailboxes()
    {
        return $this->mailboxes;
    }

    public function getMailbox($name = 'INBOX')
    {
        if(isset($this->initializeMailboxes()->mailbox[$name])) {
            return $this->mailbox[$name];
        } else {
            throw new InvalidMailboxNameException("Mailbox '$name' not found.");
        }
    }

    public function isConnected()
    {
        return (bool) $this->connected;
    }

    public function isInitialized()
    {
        return (bool) $this->initialized;
    }

    public function using($name)
    {
        if($name !== $this->usedMailbox) {
            $this->driver->using($name);
            $this->usedMailbox = $name;
        }

        return $this;
    }

    public function createMailbox($name)
    {
        if(!$this->driver->createMailbox($name)) {
            throw new DriverException("Mailbox '$name' couldn't be created.");
        }
        return $this->mailboxes[$name] = new Selection($this, $name);
    }

    public function renameMailbox($from, $to)
    {
        if(!isset($this->mailboxes[$from])) {
            throw new InvalidMailboxNameException("Mailbox '$from' not found.");
        }
        if(isset($this->mailboxes[$to])) {
            throw new InvalidMailboxNameException("Mailbox '$to' already exists.");
        }

        if(!$this->driver->renameMailbox($from, $to)) {
            throw new DriverException("Mailbox '$from' could not be renamed.");
        }

        $this->mailboxes[$to] = new Selection($this, $to);

        unset($this->mailboxes[$from]);

        return $this->mailboxes[$to];
    }

    public function deleteMailbox($name)
    {
        if(!isset($this->mailboxes[$name])) {
            throw new InvalidMailboxNameException("Mailbox '$name' not found.");
        }
        if($this->driver->deleteMailbox($name)) {
            throw new DriverException("Mailbox '$name' could not be deleted.");
        }
        unset($this->mailboxes[$name]);
        return $this;
    }

    public function moveMail($from, $id, $to)
    {
        if($from === $to) {
            throw new InvalidMailboxNameException("Cannot move mail to mailbox, where it is.");
        }

        $this->using($from);
        $this->driver->moveMails($to, array($id));
        return $this;
    }

    public function copyMail($from, $id, $to)
    {
        if($from === $to) {
            throw new InvalidMailboxNameException("Cannot copy mail to mailbox, where it is.");
        }

        $this->using($from);
        $this->driver->copyMails($to, array($id));
        return $this;
    }

    public function deleteMail($from, $id)
    {
        $this->using($from)->driver->deleteMails(array($id));
        return $this;
    }

    public function flush()
    {
        $this->driver->flush();
        return $this;
    }

    public function setMailFlags($mailbox, $id, $flag, $bool)
    {
        $this->using($mailbox)->driver->setMailFlags($id, $flag, $bool);
    }

    protected function connect()
    {
        if(!$this->connected) {
            if(!$this->driver->connect()) {
                throw new ConnectionException("Could not connect to mail server.");
            }
            $this->serverName = $this->driver->getServerName();
            $this->connected = TRUE;
        }
        return $this;
    }

    protected function initializeMailboxes()
    {
        if(!$this->initialized) {
            foreach($this->connect()->driver->getMailboxes() as $mailbox) {
                $this->mailboxes[$mailbox] = new Selection($this, $mailbox);
            }
            $this->initialized = TRUE;
        }
        return $this;
    }
}
