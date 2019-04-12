<?php
declare(strict_types=1);

namespace Ublaboo\DataGrid\Tests\Files;

use LeanMapper;

/**
 * @property int $id
 * @property string $name
 * @property XTestingLMDataGridEntity2|NULL $girlfriend
 */
class XTestingLMDataGridEntity extends LeanMapper\Entity
{

	private $age;


	public function getAge()
	{
		return $this->age;
	}


	public function setAge($age)
	{
		$this->age = $age;
	}

}
