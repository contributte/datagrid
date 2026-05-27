<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Benchmarks;

use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\DataSource\ArrayDataSource;
use Contributte\Datagrid\Filter\FilterMultiSelect;
use Contributte\Datagrid\Filter\FilterRange;
use Contributte\Datagrid\Filter\FilterSelect;
use Contributte\Datagrid\Filter\FilterText;
use PhpBench\Attributes as Bench;

/**
 * Benchmarks for filter application on ArrayDataSource.
 *
 * Tests individual filter types (text, select, range, multi-select)
 * as well as combined / sequential filter application across
 * different dataset sizes.
 */
class FilterApplicationBench
{

	private const STATUSES = ['active', 'inactive', 'pending', 'archived', 'deleted'];

	private const FIRST_NAMES = ['Alice', 'Bob', 'Charlie', 'Diana', 'Eve', 'Frank', 'Grace', 'Henry'];

	private const LAST_NAMES = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis'];

	private array $data = [];

	private Datagrid $grid;

	// ------------------------------------------------------------------
	// FilterText: split-word search (default, disjunction)
	// ------------------------------------------------------------------

	/**
	 * @param array{row_count: int} $params
	 */
	#[Bench\Revs(100)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUp')]
	#[Bench\ParamProviders('provideRowCounts')]
	public function benchFilterTextWithWordSplitting(array $params): void
	{
		$filter = new FilterText($this->grid, 'name', 'Name', ['name']);
		$filter->setValue('Alice Smith');

		$ds = new ArrayDataSource($this->data);
		$ds->filter([$filter]);
		$ds->getCount();
	}

	// ------------------------------------------------------------------
	// FilterText: no word splitting (whole-phrase substring match)
	// ------------------------------------------------------------------

	/**
	 * @param array{row_count: int} $params
	 */
	#[Bench\Revs(100)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUp')]
	#[Bench\ParamProviders('provideRowCounts')]
	public function benchFilterTextWithoutWordSplitting(array $params): void
	{
		$filter = new FilterText($this->grid, 'name', 'Name', ['name']);
		$filter->setValue('Alice Smith');
		$filter->setSplitWordsSearch(false);

		$ds = new ArrayDataSource($this->data);
		$ds->filter([$filter]);
		$ds->getCount();
	}

	// ------------------------------------------------------------------
	// FilterText: conjunction search (all words must match)
	// ------------------------------------------------------------------

	/**
	 * @param array{row_count: int} $params
	 */
	#[Bench\Revs(100)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUp')]
	#[Bench\ParamProviders('provideRowCounts')]
	public function benchFilterTextConjunction(array $params): void
	{
		$filter = new FilterText($this->grid, 'name', 'Name', ['name']);
		$filter->setValue('Alice Smith');
		$filter->setConjunctionSearch(true);

		$ds = new ArrayDataSource($this->data);
		$ds->filter([$filter]);
		$ds->getCount();
	}

	// ------------------------------------------------------------------
	// FilterSelect
	// ------------------------------------------------------------------

	/**
	 * @param array{row_count: int} $params
	 */
	#[Bench\Revs(100)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUp')]
	#[Bench\ParamProviders('provideRowCounts')]
	public function benchFilterSelect(array $params): void
	{
		$options = array_combine(self::STATUSES, array_map('ucfirst', self::STATUSES));
		$filter = new FilterSelect($this->grid, 'status', 'Status', $options, 'status');
		$filter->setValue('active');

		$ds = new ArrayDataSource($this->data);
		$ds->filter([$filter]);
		$ds->getCount();
	}

	// ------------------------------------------------------------------
	// FilterRange (numeric)
	// ------------------------------------------------------------------

	/**
	 * @param array{row_count: int} $params
	 */
	#[Bench\Revs(100)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUp')]
	#[Bench\ParamProviders('provideRowCounts')]
	public function benchFilterRangeFullRange(array $params): void
	{
		$filter = new FilterRange($this->grid, 'price', 'Price From', 'price', 'Price To');
		$filter->setValue(['from' => 100, 'to' => 500]);

		$ds = new ArrayDataSource($this->data);
		$ds->filter([$filter]);
		$ds->getCount();
	}

	/**
	 * @param array{row_count: int} $params
	 */
	#[Bench\Revs(100)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUp')]
	#[Bench\ParamProviders('provideRowCounts')]
	public function benchFilterRangeFromOnly(array $params): void
	{
		$filter = new FilterRange($this->grid, 'price', 'Price From', 'price', 'Price To');
		$filter->setValue(['from' => 400, 'to' => '']);

		$ds = new ArrayDataSource($this->data);
		$ds->filter([$filter]);
		$ds->getCount();
	}

	// ------------------------------------------------------------------
	// FilterMultiSelect: varying number of selected values
	// ------------------------------------------------------------------

	/**
	 * @param array{row_count: int, selected: string[]} $params
	 */
	#[Bench\Revs(100)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUp')]
	#[Bench\ParamProviders('provideMultiSelectParams')]
	public function benchFilterMultiSelect(array $params): void
	{
		$options = array_combine(self::STATUSES, array_map('ucfirst', self::STATUSES));
		$filter = new FilterMultiSelect($this->grid, 'status', 'Status', $options, 'status');
		$filter->setValue($params['selected']);

		$ds = new ArrayDataSource($this->data);
		$ds->filter([$filter]);
		$ds->getCount();
	}

	// ------------------------------------------------------------------
	// Multiple filters applied in sequence
	// ------------------------------------------------------------------

	/**
	 * @param array{row_count: int} $params
	 */
	#[Bench\Revs(100)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUp')]
	#[Bench\ParamProviders('provideRowCounts')]
	public function benchMultipleFiltersSequential(array $params): void
	{
		$options = array_combine(self::STATUSES, array_map('ucfirst', self::STATUSES));

		$filterText = new FilterText($this->grid, 'name', 'Name', ['name']);
		$filterText->setValue('Alice');

		$filterSelect = new FilterSelect($this->grid, 'status', 'Status', $options, 'status');
		$filterSelect->setValue('active');

		$filterRange = new FilterRange($this->grid, 'price', 'Price From', 'price', 'Price To');
		$filterRange->setValue(['from' => 100, 'to' => 800]);

		$ds = new ArrayDataSource($this->data);
		$ds->filter([$filterText, $filterSelect, $filterRange]);
		$ds->getCount();
	}

	/**
	 * Apply all four filter types in sequence.
	 *
	 * @param array{row_count: int} $params
	 */
	#[Bench\Revs(100)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUp')]
	#[Bench\ParamProviders('provideRowCounts')]
	public function benchAllFilterTypesCombined(array $params): void
	{
		$options = array_combine(self::STATUSES, array_map('ucfirst', self::STATUSES));

		$filterText = new FilterText($this->grid, 'name', 'Name', ['name']);
		$filterText->setValue('Bob');

		$filterRange = new FilterRange($this->grid, 'price', 'Price From', 'price', 'Price To');
		$filterRange->setValue(['from' => 50, 'to' => 600]);

		$filterMulti = new FilterMultiSelect($this->grid, 'status', 'Status', $options, 'status');
		$filterMulti->setValue(['active', 'pending']);

		$ds = new ArrayDataSource($this->data);
		$ds->filter([$filterText, $filterRange, $filterMulti]);
		$ds->getCount();
	}

	// ------------------------------------------------------------------
	// Param providers
	// ------------------------------------------------------------------

	/**
	 * @return array<string, array{row_count: int}>
	 */
	public function provideRowCounts(): array
	{
		return [
			'100 rows' => ['row_count' => 100],
			'500 rows' => ['row_count' => 500],
			'2000 rows' => ['row_count' => 2000],
		];
	}

	/**
	 * @return array<string, array{row_count: int, selected: string[]}>
	 */
	public function provideMultiSelectParams(): array
	{
		return [
			'100 rows, 1 selected' => ['row_count' => 100, 'selected' => ['active']],
			'100 rows, 3 selected' => ['row_count' => 100, 'selected' => ['active', 'pending', 'archived']],
			'500 rows, 1 selected' => ['row_count' => 500, 'selected' => ['active']],
			'500 rows, 3 selected' => ['row_count' => 500, 'selected' => ['active', 'pending', 'archived']],
			'2000 rows, 1 selected' => ['row_count' => 2000, 'selected' => ['active']],
			'2000 rows, 3 selected' => ['row_count' => 2000, 'selected' => ['active', 'pending', 'archived']],
			'2000 rows, 5 selected' => ['row_count' => 2000, 'selected' => ['active', 'pending', 'archived', 'inactive', 'deleted']],
		];
	}

	// ------------------------------------------------------------------
	// Setup
	// ------------------------------------------------------------------

	/**
	 * @param array{row_count: int} $params
	 */
	public function setUp(array $params): void
	{
		$this->grid = new Datagrid();
		$this->data = [];

		$firstNames = self::FIRST_NAMES;
		$lastNames = self::LAST_NAMES;
		$statuses = self::STATUSES;

		for ($i = 0; $i < $params['row_count']; $i++) {
			$this->data[] = [
				'id' => $i + 1,
				'name' => $firstNames[$i % count($firstNames)] . ' ' . $lastNames[$i % count($lastNames)],
				'status' => $statuses[$i % count($statuses)],
				'price' => round(($i * 7.3 + 10) % 1000, 2),
				'date' => '2024-' . str_pad((string) (($i % 12) + 1), 2, '0', STR_PAD_LEFT) . '-'
					. str_pad((string) (($i % 28) + 1), 2, '0', STR_PAD_LEFT),
			];
		}
	}

}
