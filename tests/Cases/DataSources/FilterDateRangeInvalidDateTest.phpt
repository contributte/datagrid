<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases\DataSources;

use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\DataSource\ArrayDataSource;
use Contributte\Datagrid\Filter\FilterDate;
use Contributte\Datagrid\Filter\FilterDateRange;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;
use DateTime;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';
require __DIR__ . '/../../Files/TestingDatagridFactory.php';

/**
 * Test case for handling invalid date values in FilterDateRange and FilterDate
 *
 * @see https://github.com/contributte/datagrid/issues/711
 */
final class FilterDateRangeInvalidDateTest extends TestCase
{

	private ArrayDataSource $ds;

	private Datagrid $grid;

	/** @var array<int, array<string, mixed>> */
	private array $data = [
		['id' => 1, 'name' => 'John Doe', 'created' => '2023-01-15'],
		['id' => 2, 'name' => 'Jane Doe', 'created' => '2023-02-20'],
		['id' => 3, 'name' => 'Bob Smith', 'created' => '2023-03-25'],
		['id' => 4, 'name' => 'Alice Brown', 'created' => '2023-04-30'],
	];

	public function setUp(): void
	{
		$this->ds = new ArrayDataSource($this->data);
		$factory = new TestingDatagridFactory();
		$this->grid = $factory->createTestingDatagrid();
	}

	/**
	 * Test that invalid "from" date in FilterDateRange doesn't throw exception
	 */
	public function testFilterDateRangeWithInvalidFromDate(): void
	{
		$filter = new FilterDateRange($this->grid, 'created', 'Created', 'created', '-');
		$filter->setValue(['from' => 'invalid-date', 'to' => null]);

		$this->ds->filter([$filter]);

		// Should return all data when invalid date is provided (filter is ignored)
		Assert::same(4, $this->ds->getCount());
	}

	/**
	 * Test that invalid "to" date in FilterDateRange doesn't throw exception
	 */
	public function testFilterDateRangeWithInvalidToDate(): void
	{
		$filter = new FilterDateRange($this->grid, 'created', 'Created', 'created', '-');
		$filter->setValue(['from' => null, 'to' => 'not-a-date']);

		$this->ds->filter([$filter]);

		// Should return all data when invalid date is provided (filter is ignored)
		Assert::same(4, $this->ds->getCount());
	}

	/**
	 * Test that both invalid dates in FilterDateRange don't throw exception
	 */
	public function testFilterDateRangeWithBothInvalidDates(): void
	{
		$filter = new FilterDateRange($this->grid, 'created', 'Created', 'created', '-');
		$filter->setValue(['from' => 'abc', 'to' => 'xyz']);

		$this->ds->filter([$filter]);

		// Should return all data when both invalid dates are provided
		Assert::same(4, $this->ds->getCount());
	}

	/**
	 * Test that valid "from" date with invalid "to" date works correctly
	 */
	public function testFilterDateRangeWithValidFromAndInvalidTo(): void
	{
		$filter = new FilterDateRange($this->grid, 'created', 'Created', 'created', '-');
		$filter->setFormat('Y-m-d', 'yyyy-mm-dd');
		$filter->setValue(['from' => '2023-02-01', 'to' => 'invalid']);

		$this->ds->filter([$filter]);

		// Should filter based on valid "from" date only
		// Records from 2023-02-01 onwards: Jane (02-20), Bob (03-25), Alice (04-30)
		Assert::same(3, $this->ds->getCount());
	}

	/**
	 * Test that invalid "from" date with valid "to" date works correctly
	 */
	public function testFilterDateRangeWithInvalidFromAndValidTo(): void
	{
		$filter = new FilterDateRange($this->grid, 'created', 'Created', 'created', '-');
		$filter->setFormat('Y-m-d', 'yyyy-mm-dd');
		$filter->setValue(['from' => 'invalid', 'to' => '2023-02-28']);

		$this->ds->filter([$filter]);

		// Should filter based on valid "to" date only
		// Records up to 2023-02-28: John (01-15), Jane (02-20)
		Assert::same(2, $this->ds->getCount());
	}

	/**
	 * Test that invalid date in FilterDate doesn't throw exception
	 */
	public function testFilterDateWithInvalidDate(): void
	{
		$filter = new FilterDate($this->grid, 'created', 'Created', 'created');
		$filter->setValue('not-a-valid-date');

		$this->ds->filter([$filter]);

		// Should return all data when invalid date is provided (filter is ignored)
		Assert::same(4, $this->ds->getCount());
	}

	/**
	 * Test that valid dates still work correctly in FilterDateRange
	 */
	public function testFilterDateRangeWithValidDates(): void
	{
		$filter = new FilterDateRange($this->grid, 'created', 'Created', 'created', '-');
		$filter->setFormat('Y-m-d', 'yyyy-mm-dd');
		$filter->setValue(['from' => '2023-02-01', 'to' => '2023-03-31']);

		$this->ds->filter([$filter]);

		// Should filter correctly: Jane (02-20), Bob (03-25)
		Assert::same(2, $this->ds->getCount());
	}

	/**
	 * Test that valid dates still work correctly in FilterDate
	 */
	public function testFilterDateWithValidDate(): void
	{
		$filter = new FilterDate($this->grid, 'created', 'Created', 'created');
		$filter->setFormat('Y-m-d', 'yyyy-mm-dd');
		$filter->setValue('2023-02-20');

		$this->ds->filter([$filter]);

		// Should filter correctly: Jane (02-20)
		Assert::same(1, $this->ds->getCount());
	}

	/**
	 * Test with DateTime objects in data
	 */
	public function testFilterDateRangeWithDateTimeObjects(): void
	{
		$dataWithDateTime = [
			['id' => 1, 'name' => 'John', 'created' => new DateTime('2023-01-15')],
			['id' => 2, 'name' => 'Jane', 'created' => new DateTime('2023-02-20')],
			['id' => 3, 'name' => 'Bob', 'created' => new DateTime('2023-03-25')],
		];

		$ds = new ArrayDataSource($dataWithDateTime);

		$filter = new FilterDateRange($this->grid, 'created', 'Created', 'created', '-');
		$filter->setValue(['from' => 'invalid-date', 'to' => null]);

		$ds->filter([$filter]);

		// Should return all data when invalid date is provided
		Assert::same(3, $ds->getCount());
	}

	/**
	 * Test with empty string dates (edge case)
	 */
	public function testFilterDateRangeWithEmptyStrings(): void
	{
		$filter = new FilterDateRange($this->grid, 'created', 'Created', 'created', '-');
		$filter->setValue(['from' => '', 'to' => '']);

		$this->ds->filter([$filter]);

		// Empty strings should be treated as no filter
		Assert::same(4, $this->ds->getCount());
	}

	/**
	 * Test with special characters in date string
	 */
	public function testFilterDateRangeWithSpecialCharacters(): void
	{
		$filter = new FilterDateRange($this->grid, 'created', 'Created', 'created', '-');
		$filter->setValue(['from' => '<script>alert(1)</script>', 'to' => "'; DROP TABLE users;--"]);

		$this->ds->filter([$filter]);

		// Should handle malicious input gracefully and return all data
		Assert::same(4, $this->ds->getCount());
	}

}

(new FilterDateRangeInvalidDateTest())->run();
