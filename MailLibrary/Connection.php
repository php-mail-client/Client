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
