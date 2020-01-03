<?php

namespace PhpMailClient;

class Contact
{

	/** @var string|NULL */
	private $mailbox;

	/** @var string|NULL */
	private $host;

	/** @var string|NULL */
	private $personal;

	/** @var string|NULL */
	private $adl;

	public function __construct(?string $mailbox = NULL, ?string $host = NULL, ?string $personal = NULL, ?string $adl = NULL)
	{
		$this->mailbox = $mailbox;
		$this->host = $host;
		$this->personal = $personal;
		$this->adl = $adl;
	}

	public function __toString(): string
	{
		$address = $this->getName() ? '"' . $this->getName() . '" ' : '';
		$address .= $this->getAdl() ? $this->getAdl() . ':' : '';
		$address .= '<' . $this->getEmail() . '>';
		return $address;
	}

	public function getEmail(): string
	{
		return $this->mailbox . '@' . $this->host;
	}

	public function getName(): ?string
	{
		return $this->personal;
	}

	public function getAdl(): ?string
	{
		return $this->adl;
	}

}
