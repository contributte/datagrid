<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases\DataSources;

use Contributte\Datagrid\DataSource\ArrayDataSource;
use Contributte\Datagrid\Filter\FilterDate;
use Contributte\Datagrid\Filter\FilterDateRange;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;
use Tester\Assert;

require __DIR__ . '/BaseDataSourceTest.phpt';


final class ArrayDataSourceTest extends BaseDataSourceTest
{

	public function setUp(): void
	{
		$this->ds = new ArrayDataSource($this->data);
		$factory = new TestingDatagridFactory();
		$this->grid = $factory->createTestingDatagrid();
	}

	public function testFilterDateWithInvalidValue(): void
	{
		$ds = new ArrayDataSource([
			['id' => 1, 'date' => '1. 1. 2020'],
			['id' => 2, 'date' => '15. 6. 2021'],
		]);

		$filter = new FilterDate($this->grid, 'date', 'Date', 'date');
		$filter->setValue('not-a-date');

		$ds->filter([$filter]);

		Assert::same(0, $ds->getCount());
	}

	public function testFilterDateRangeWithInvalidFromValue(): void
	{
		$ds = new ArrayDataSource([
			['id' => 1, 'date' => '1. 1. 2020'],
			['id' => 2, 'date' => '15. 6. 2021'],
		]);

		$filter = new FilterDateRange($this->grid, 'date', 'Date', 'date', 'Date to');
		$filter->setValue(['from' => 'not-a-date', 'to' => '']);

		$ds->filter([$filter]);

		Assert::same(0, $ds->getCount());
	}

	public function testFilterDateRangeWithInvalidToValue(): void
	{
		$ds = new ArrayDataSource([
			['id' => 1, 'date' => '1. 1. 2020'],
			['id' => 2, 'date' => '15. 6. 2021'],
		]);

		$filter = new FilterDateRange($this->grid, 'date', 'Date', 'date', 'Date to');
		$filter->setValue(['from' => '', 'to' => 'not-a-date']);

		$ds->filter([$filter]);

		Assert::same(0, $ds->getCount());
	}

}

(new ArrayDataSourceTest())->run();
