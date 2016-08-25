<?php

namespace Ublaboo\DataGrid\Tests\Cases\DataSources;
use Ublaboo;

require __DIR__ . '/BaseDataSourceTest.phpt';


final class ArrayDataSourceTest extends BaseDataSourceTest
{

	public function setUp()
	{
		$this->ds = new Ublaboo\DataGrid\DataSource\ArrayDataSource($this->data);
		$factory = new Ublaboo\DataGrid\Tests\Files\XTestingDataGridFactory;
		$this->grid = $factory->createXTestingDataGrid();
	}
}


$test_case = new ArrayDataSourceTest;
$test_case->run();
