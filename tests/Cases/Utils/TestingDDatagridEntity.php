<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases\Utils;

class TestingDDatagridEntity
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

	/** @ORM\Column(type="string", enumType="GenderEnum") */
	private ?GenderEnum $gender;

	private ?TestingDDatagridEntity $partner = null;

	public function __construct(array $args)
	{
		$this->id = $args['id'];
		$this->age = $args['age'];
		$this->name = $args['name'];
		$this->gender = $args['gender'] ?? null;
	}

	final public function getId(): int
	{
		return $this->id;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getAge(): int
	{
		return $this->age;
	}

	public function getGender(): ?GenderEnum
	{
		return $this->gender;
	}

	public function setPartner(TestingDDatagridEntity $partner): void
	{
		$this->partner = $partner;
	}

	public function getPartner(): ?TestingDDatagridEntity
	{
		return $this->partner;
	}

}
