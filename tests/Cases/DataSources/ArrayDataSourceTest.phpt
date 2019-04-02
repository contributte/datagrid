<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Tests\Cases\DataSources;

use Ublaboo;

require __DIR__ . '/BaseDataSourceTest.phpt';


final class ArrayDataSourceTest extends BaseDataSourceTest
{

	public function setUp(): void
	{
		$this->ds = new Ublaboo\DataGrid\DataSource\ArrayDataSource($this->data);
		$factory = new Ublaboo\DataGrid\Tests\Files\TestingDataGridFactory();
		$this->grid = $factory->createTestingDataGrid();
	}

}


$test_case = new ArrayDataSourceTest();
$test_case->run();
