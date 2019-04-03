<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\Tests\Cases\Utils;

/**
 * @property int $id
 * @property string $name
 */
class TestingLMDataGridEntity2 extends LeanMapper\Entity
{

	/**
	 * @var int
	 */
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
