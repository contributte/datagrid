<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Benchmarks;

use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\DataSource\ArrayDataSource;
use Contributte\Datagrid\Filter\FilterText;
use Contributte\Datagrid\Utils\Sorting;
use PhpBench\Attributes as Bench;

/**
 * Benchmarks for the DataModel filterData() flow exercised directly
 * on ArrayDataSource — covering filtering, sorting and pagination
 * at different dataset sizes.
 */
class DataModelBench
{

	private array $rows = [];

	private Datagrid $grid;

	/**
	 * @param array{row_count: int} $params
	 */
	public function setUp(array $params): void
	{
		$this->grid = new Datagrid();
		$this->rows = [];

		$names = ['Alice', 'Bob', 'Charlie', 'Diana', 'Eve', 'Frank', 'Grace', 'Hank'];
		$cities = ['Prague', 'Berlin', 'London', 'Paris', 'Vienna', 'Rome', 'Madrid', 'Oslo'];

		for ($i = 0; $i < $params['row_count']; $i++) {
			$this->rows[] = [
				'id' => $i + 1,
				'name' => $names[$i % count($names)],
				'city' => $cities[$i % count($cities)],
				'age' => ($i % 60) + 18,
			];
		}
	}

	// ------------------------------------------------------------------
	// 1. Filtering with FilterText
	// ------------------------------------------------------------------

	/**
	 * Filter rows using a FilterText on the "name" column.
	 *
	 * @param array{row_count: int} $params
	 */
	#[Bench\Revs(100)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUp')]
	#[Bench\ParamProviders('provideRowCounts')]
	public function benchFilterText(array $params): void
	{
		$dataSource = new ArrayDataSource($this->rows);

		$filter = new FilterText($this->grid, 'name', 'Name', ['name']);
		$filter->setValue('Ali');

		$dataSource->filter([$filter]);
		$dataSource->getData();
	}

	/**
	 * Filter rows using a FilterText that searches across two columns.
	 *
	 * @param array{row_count: int} $params
	 */
	#[Bench\Revs(100)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUp')]
	#[Bench\ParamProviders('provideRowCounts')]
	public function benchFilterTextMultiColumn(array $params): void
	{
		$dataSource = new ArrayDataSource($this->rows);

		$filter = new FilterText($this->grid, 'search', 'Search', ['name', 'city']);
		$filter->setValue('a');

		$dataSource->filter([$filter]);
		$dataSource->getData();
	}

	// ------------------------------------------------------------------
	// 2. Sorting
	// ------------------------------------------------------------------

	/**
	 * Sort rows by a single column in ascending order.
	 *
	 * @param array{row_count: int} $params
	 */
	#[Bench\Revs(100)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUp')]
	#[Bench\ParamProviders('provideRowCounts')]
	public function benchSortAsc(array $params): void
	{
		$dataSource = new ArrayDataSource($this->rows);
		$sorting = new Sorting(['name' => 'ASC']);

		$dataSource->sort($sorting);
		$dataSource->getData();
	}

	/**
	 * Sort rows by a single column in descending order.
	 *
	 * @param array{row_count: int} $params
	 */
	#[Bench\Revs(100)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUp')]
	#[Bench\ParamProviders('provideRowCounts')]
	public function benchSortDesc(array $params): void
	{
		$dataSource = new ArrayDataSource($this->rows);
		$sorting = new Sorting(['name' => 'DESC']);

		$dataSource->sort($sorting);
		$dataSource->getData();
	}

	// ------------------------------------------------------------------
	// 3. Pagination (limit / offset)
	// ------------------------------------------------------------------

	/**
	 * Paginate from the beginning of the dataset.
	 *
	 * @param array{row_count: int} $params
	 */
	#[Bench\Revs(500)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUp')]
	#[Bench\ParamProviders('provideRowCounts')]
	public function benchPaginateFirstPage(array $params): void
	{
		$dataSource = new ArrayDataSource($this->rows);

		$dataSource->limit(0, 20);
		$dataSource->getData();
	}

	/**
	 * Paginate from the middle of the dataset.
	 *
	 * @param array{row_count: int} $params
	 */
	#[Bench\Revs(500)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUp')]
	#[Bench\ParamProviders('provideRowCounts')]
	public function benchPaginateMiddlePage(array $params): void
	{
		$dataSource = new ArrayDataSource($this->rows);
		$offset = (int) ($params['row_count'] / 2);

		$dataSource->limit($offset, 20);
		$dataSource->getData();
	}

	// ------------------------------------------------------------------
	// Combined: filter + sort + paginate (full filterData flow)
	// ------------------------------------------------------------------

	/**
	 * Simulate the full DataModel::filterData() flow:
	 * filter -> sort -> limit -> getData.
	 *
	 * @param array{row_count: int} $params
	 */
	#[Bench\Revs(100)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUp')]
	#[Bench\ParamProviders('provideRowCounts')]
	public function benchFullFilterDataFlow(array $params): void
	{
		$dataSource = new ArrayDataSource($this->rows);

		// 1. Filter
		$filter = new FilterText($this->grid, 'name', 'Name', ['name']);
		$filter->setValue('a');
		$dataSource->filter([$filter]);

		// 2. Sort
		$sorting = new Sorting(['name' => 'ASC']);
		$dataSource->sort($sorting);

		// 3. Paginate
		$dataSource->limit(0, 20);

		// 4. Retrieve data
		$dataSource->getData();
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
			'50 rows' => ['row_count' => 50],
			'200 rows' => ['row_count' => 200],
			'1000 rows' => ['row_count' => 1000],
		];
	}

}
