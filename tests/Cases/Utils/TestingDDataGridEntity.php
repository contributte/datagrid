<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Tests\Cases\Utils;

class TestingDDataGridEntity
{

	/**
	 * @var int
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	private mixed $id;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	private mixed $name;

	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	private mixed $age;

	private ?TestingDDataGridEntity $partner = null;

	final public function getId(): int
	{
		return $this->id;
	}

	public function __construct(array $args)
	{
		$this->id = $args['id'];
		$this->age = $args['age'];
		$this->name = $args['name'];
	}

	public function getName(): string
	{
		return $this->name;
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
