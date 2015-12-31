<?php
/**
 * @author Tomáš Blatný
 */

namespace greeny\EmailClient;

class Mailbox
{

	/** @var IDriver */
	private $driver;

	/** @var string */
	private $originalName;

	/** @var string */
	private $name;


	/**
	 * @param IDriver $driver
	 * @param string $originalName
	 * @param string $name
	 */
	public function __construct(IDriver $driver, $originalName, $name)
	{
		$this->driver = $driver;
		$this->originalName = $originalName;
		$this->name = $name;
	}


	/**
	 * Returns original mailbox name
	 *
	 * @return string
	 */
	public function getOriginalName()
	{
		return $this->originalName;
	}


	/**
	 * Returns mailbox name without server part
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

}
