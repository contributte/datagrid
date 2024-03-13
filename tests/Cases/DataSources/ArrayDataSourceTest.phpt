<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases\DataSources;

use Contributte\Datagrid\DataSource\ArrayDataSource;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;

require __DIR__ . '/BaseDataSourceTest.phpt';


final class ArrayDataSourceTest extends BaseDataSourceTest
{

	public function setUp(): void
	{
		$this->ds = new ArrayDataSource($this->data);
		$factory = new TestingDatagridFactory();
		$this->grid = $factory->createTestingDatagrid();
	}

}

(new ArrayDataSourceTest())->run();
