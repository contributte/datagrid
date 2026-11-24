<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases\DataSources;

use Contributte\Datagrid\DataSource\ArrayDataSource;
use Contributte\Datagrid\Filter\FilterDate;
use Contributte\Datagrid\Filter\FilterDateRange;
use Contributte\Datagrid\Filter\FilterRange;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;
use Contributte\Datagrid\Utils\Sorting;
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

	public function testSortByCollateLocale(): void
	{
		if (!class_exists(\Collator::class) || setlocale(LC_COLLATE, 'en_US.UTF-8', 'en_US.utf8') === false) {
			$this->skip('ext-intl or en_US.UTF-8 locale is not available');
		}

		try {
			$rows = [['name' => 'b'], ['name' => 'Á'], ['name' => 'c'], ['name' => 'a']];

			$ds = new ArrayDataSource($rows);
			$ds->sort(new Sorting(['name' => 'ASC']));
			Assert::same(['a', 'Á', 'b', 'c'], array_column($ds->getData(), 'name'));

			$ds = new ArrayDataSource($rows);
			$ds->sort(new Sorting(['name' => 'DESC']));
			Assert::same(['c', 'b', 'Á', 'a'], array_column($ds->getData(), 'name'));
		} finally {
			setlocale(LC_COLLATE, 'C');
		}
	}

}

(new ArrayDataSourceTest())->run();
