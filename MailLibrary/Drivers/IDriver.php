<?php
/**
 * @package MailLibrary
 * @author Tomáš Blatný
 */

namespace greeny\MailLibrary\Drivers;

use greeny\MailLibrary\Filter;
use greeny\MailLibrary\Structure;

/**
 * Interface for any driver
 */
interface IDriver
{
    function __construct(array $data);
    function connect();

    function getServerName();
    function getMailboxes();

    function getMails(Filter $filter);
    function getMailHeaders($id);
    function getMailStructure($id);

    function getBody(Structure $structure, $index);
    function getFullBody(Structure $structure, array $indexes);
    function getAttachments(Structure $structure, array $indexes);
    function getAttachedMails(Structure $structure, array $indexes);
    function getMedia(Structure $structure, array $indexes);
}
