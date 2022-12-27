<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Tests\Cases\DataSources;

use Ublaboo\DataGrid\DataSource\ArrayDataSource;
use Ublaboo\DataGrid\Tests\Files\TestingDataGridFactory;

require __DIR__ . '/BaseDataSourceTest.phpt';


final class ArrayDataSourceTest extends BaseDataSourceTest
{

	public function setUp(): void
	{
		$this->ds = new ArrayDataSource($this->data);
		$factory = new TestingDataGridFactory();
		$this->grid = $factory->createTestingDataGrid();
	}

}

(new ArrayDataSourceTest())->run();
