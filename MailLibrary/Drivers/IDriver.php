<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\MailLibrary\Drivers;

use greeny\MailLibrary\DriverException;
use greeny\MailLibrary\Mail;
use greeny\MailLibrary\Mailbox;
use greeny\MailLibrary\Structures\IStructure;

interface IDriver {
    /**
     * Connects to server
     * @throws DriverException
     */
    function connect();

    /**
     * Flushes changes to server
     * @throws DriverException
     */
    function flush();

    /**
     * Gets all mailboxes
     * @return array of string
     * @throws DriverException
     */
    function getMailboxes();

    /**
     * Creates new mailbox
     * @param string $name
     * @throws DriverException
     */
    function createMailbox($name);

    /**
     * Renames mailbox
     * @param string $from
     * @param string $to
     * @throws DriverException
     */
    function renameMailbox($from, $to);

    /**
     * Deletes mailbox
     * @param string $name
     * @throws DriverException
     */
    function deleteMailbox($name);

    /**
     * Switches current mailbox
     * @param string $name
     * @throws DriverException
     */
    function switchMailbox($name);

    /**
     * Finds UIDs of mails by filter
     *
     * @param array  $filters
     * @param int    $limit
     * @param int    $offset
     * @param int    $orderBy
     * @param string $orderType
     * @return array of UIDs
     */
    function getMailIds(array $filters, $limit = 0, $offset = 0, $orderBy = Mail::ORDER_DATE, $orderType = 'ASC');

    /**
     * Checks if filter is applicable for this driver
     * @param string $key
     * @param mixed  $value
     * @throws DriverException
     */
    function checkFilter($key, $value = NULL);

    /**
     * Gets mail headers
     * @param int $mailId
     * @return array of name => value
     */
    function getHeaders($mailId);

    /**
     * Creates structure for mail
     * @param int     $mailId
     * @param Mailbox $mailbox
     * @return IStructure
     */
    function getStructure($mailId, Mailbox $mailbox);

    /**
     * Gets part of body
     * @param int   $mailId
     * @param array $data
     * @return string
     */
    function getBody($mailId, array $data);

    /**
     * Gets flags for mail
     * @param int $mailId
     * @return array
     */
    function getFlags($mailId);

    /**
     * Sets one flag for mail
     * @param int    $mailId
     * @param string $flag
     * @param bool   $value
     * @throws DriverException
     */
    function setFlag($mailId, $flag, $value);

    /**
     * Copies mail to another mailbox
     * @param int    $mailId
     * @param string $toMailbox
     * @throws DriverException
     */
    function copyMail($mailId, $toMailbox);

    /**
     * Moves mail to another mailbox
     * @param int    $mailId
     * @param string $toMailbox
     * @throws DriverException
     */
    function moveMail($mailId, $toMailbox);

    /**
     * Deletes mail
     * @param int $mailId
     * @throws DriverException
     */
    function deleteMail($mailId);

    /**
     * @param $mailId
     * @return array
     */
    function getOverview($mailId);

    /**
     * @param $str
     * @return string
     */
    function mimeDecode($str);
}