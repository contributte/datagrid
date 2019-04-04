<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\Tests\Cases\Utils;

class TestingDDataGridEntity
{

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 * @var int
	 */
	private $id;

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $name;

	/**
	 * @ORM\Column(type="integer")
	 * @var int
	 */
	private $age;

	/**
	 * @var TestingDDataGridEntity|null
	 */
	private $partner;


	public function __construct($args)
	{
		$this->id = $args['id'];
		$this->age = $args['age'];
		$this->name = $args['name'];
	}


	public function getName(): string
	{
		return $this->name;
	}


	final public function getId(): int
	{
		return $this->id;
	}


	public function getAge(): int
	{
		return $this->age;
	}


	public function setPartner(TestingDDataGridEntity $partner): void
	{
		$this->partner = $partner;
	}


	public function getPartner(): ?TestingDDataGridEntity
	{
		return $this->partner;
	}

}
