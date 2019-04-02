<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Tests\Cases\Utils;

/**
 * @property int $id
 * @property string $name
 * @property TestingLMDataGridEntity2|null $girlfriend
 */
class TestingLMDataGridEntity extends LeanMapper\Entity
{

	/** @var int */
	private $age;

	public function getAge(): int
	{
		return $this->age;
	}


	public function setAge(int $age): void
	{
		$this->age = $age;
	}

}
