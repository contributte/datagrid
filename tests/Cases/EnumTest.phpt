<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases;

/**
 * @phpVersion >= 8.1
 */

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/TestingDatagridFactory.php';

use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Row;
use Contributte\Datagrid\Tests\Cases\Utils\GenderEnum;
use Contributte\Datagrid\Tests\Cases\Utils\TestingDDatagridEntity;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;
use Tester\Assert;
use Tester\TestCase;

final class EnumTest extends TestCase
{

	private Datagrid $datagrid;

	public function setUp(): void
	{
		$factory = new TestingDatagridFactory();
		$this->datagrid = $factory->createTestingDatagrid();
	}

	public function testArrayWithEnum(): void
	{
		$entity = ['id' => 20, 'name' => 'John Doe', 'age' => 23, 'gender' => GenderEnum::Male];
		$row = new Row($this->datagrid, $entity, 'id');

		Assert::same(GenderEnum::Male->value, $row->getValue('gender'));
	}

	public function testDoctrineEntityWithEnum(): void
	{
		$entity = new TestingDDatagridEntity(['id' => 20, 'name' => 'John Doe', 'age' => 23, 'gender' => GenderEnum::Male]);
		$row = new Row($this->datagrid, $entity, 'id');

		Assert::same(GenderEnum::Male->value, $row->getValue('gender'));
	}

}


(new EnumTest())->run();
