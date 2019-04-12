<?php
declare(strict_types=1);

namespace Ublaboo\DataGrid\Tests\Files;

use LeanMapper;

/**
 * @property int $id
 * @property string $name
 */
class XTestingLMDataGridEntity2 extends LeanMapper\Entity
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
