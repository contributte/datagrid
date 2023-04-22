<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Tests\Cases;

/**
 * @phpVersion >= 8.1
 */

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/TestingDataGridFactory.php';

use Tester\Assert;
use Tester\TestCase;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Row;
use Ublaboo\DataGrid\Tests\Cases\Utils\GenderEnum;
use Ublaboo\DataGrid\Tests\Cases\Utils\TestingDDataGridEntity;
use Ublaboo\DataGrid\Tests\Files\TestingDataGridFactory;

final class EnumTest extends TestCase
{

	private DataGrid $grid;

	public function setUp(): void
	{
		$factory = new TestingDataGridFactory();
		$this->grid = $factory->createTestingDataGrid();
	}

	public function testDoctrineEntityWithEnum(): void
	{
		$entity = new TestingDDataGridEntity(['id' => 20, 'name' => 'John Doe', 'age' => 23, 'gender' => GenderEnum::Male]);
		$row = new Row($this->grid, $entity, 'id');

		Assert::same(GenderEnum::Male->value, $row->getValue('gender'));
	}

}


(new EnumTest())->run();
