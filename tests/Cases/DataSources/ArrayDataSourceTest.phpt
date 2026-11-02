<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases\DataSources;

use Contributte\Datagrid\DataSource\ArrayDataSource;
use Contributte\Datagrid\Filter\FilterDate;
use Contributte\Datagrid\Filter\FilterDateRange;
use Contributte\Datagrid\Filter\FilterRange;
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

	public function testPartialDateUsesConfiguredOrder(): void
	{
		$year = date('Y');
		foreach ([
			['12.5.', 'j. n. Y', '05-12'],
			['12/5', 'd/m/Y', '05-12'],
			['12/5', 'm/d/Y', '12-05'],
		] as [$value, $format, $expected]) {
			$ds = new ArrayDataSource([
				['id' => 1, 'date' => $year . '-05-12'],
				['id' => 2, 'date' => $year . '-12-05'],
			]);
			$filter = new FilterDate($this->grid, 'date', 'Date', 'date');
			$filter->setFormat($format, 'dd/mm/yyyy');
			$filter->setValue($value);
			$ds->filter([$filter]);

			Assert::same([$year . '-' . $expected], array_column($ds->getData(), 'date'));
			Assert::same($value, $filter->getValue());
		}
	}

	public function testPartialDateRange(): void
	{
		$year = date('Y');
		$ds = new ArrayDataSource([
			['id' => 1, 'date' => $year . '-05-11'],
			['id' => 2, 'date' => $year . '-05-12'],
			['id' => 3, 'date' => $year . '-05-24'],
			['id' => 4, 'date' => $year . '-05-25'],
		]);
		$filter = new FilterDateRange($this->grid, 'date', 'Date', 'date', 'To');
		$filter->setValue(['from' => '12.5.', 'to' => '24.5.']);
		$ds->filter([$filter]);

		Assert::same([2, 3], array_column($ds->getData(), 'id'));
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

	public function testFilterRangeWithZeroFromValue(): void
	{
		$ds = new ArrayDataSource([
			['id' => 1, 'name' => 'Negative', 'age' => -5, 'address' => ''],
			['id' => 2, 'name' => 'Zero', 'age' => 0, 'address' => ''],
			['id' => 3, 'name' => 'Positive', 'age' => 5, 'address' => ''],
		]);

		$filter = new FilterRange($this->grid, 'a', 'b', 'age', '-');
		$filter->setValue(['from' => '0', 'to' => '']);
		$ds->filter([$filter]);

		Assert::same(2, $ds->getCount());
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
