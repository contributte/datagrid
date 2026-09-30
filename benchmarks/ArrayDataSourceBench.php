<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Benchmarks;

use DateTime;
use DateTimeInterface;
use PhpBench\Attributes as Bench;

/**
 * Benchmarks for ArrayDataSource optimizations:
 * - Sort flatten: nested foreach vs array_merge(...)
 * - Date range filter: duplicated DateTime conversion vs single conversion
 */
class ArrayDataSourceBench
{

	private array $sortGroupedData = [];

	private array $dateRows = [];

	/**
	 * Original sort flatten with nested foreach
	 *
	 * @param array{row_count: int} $params
	 */
	#[Bench\Revs(500)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUpSortData')]
	#[Bench\ParamProviders('provideRowCounts')]
	public function benchSortFlattenForeach(array $params): void
	{
		$dataSource = [];

		foreach ($this->sortGroupedData as $i) {
			foreach ($i as $item) {
				$dataSource[] = $item;
			}
		}
	}

	/**
	 * Optimized sort flatten with array_merge spread
	 *
	 * @param array{row_count: int} $params
	 */
	#[Bench\Revs(500)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUpSortData')]
	#[Bench\ParamProviders('provideRowCounts')]
	public function benchSortFlattenArrayMerge(array $params): void
	{
		$dataSource = array_merge(...array_values($this->sortGroupedData));
	}

	/**
	 * Original date range filter with duplicated DateTime conversion
	 *
	 * @param array{row_count: int} $params
	 */
	#[Bench\Revs(100)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUpDateRows')]
	#[Bench\ParamProviders('provideRowCounts')]
	public function benchDateRangeFilterDuplicated(array $params): void
	{
		$dateFrom = new DateTime('2024-01-01');
		$dateTo = new DateTime('2024-12-31');

		foreach ($this->dateRows as $row) {
			$rowValue = $row['date'];

			// Duplicated conversion (original pattern)
			// "from" check
			if (!($rowValue instanceof DateTime)) {
				$rowValue = new DateTime($rowValue);
			}

			if ($rowValue->getTimestamp() < $dateFrom->getTimestamp()) {
				continue;
			}

			// "to" check — re-read and re-convert (original bug)
			$rowValue2 = $row['date'];

			if (!($rowValue2 instanceof DateTime)) {
				$rowValue2 = new DateTime($rowValue2);
			}

			if ($rowValue2->getTimestamp() > $dateTo->getTimestamp()) {
				continue;
			}
		}
	}

	/**
	 * Optimized date range filter with single DateTime conversion
	 *
	 * @param array{row_count: int} $params
	 */
	#[Bench\Revs(100)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUpDateRows')]
	#[Bench\ParamProviders('provideRowCounts')]
	public function benchDateRangeFilterSingle(array $params): void
	{
		$dateFrom = new DateTime('2024-01-01');
		$dateTo = new DateTime('2024-12-31');

		foreach ($this->dateRows as $row) {
			$rowValue = $row['date'];

			// Single conversion (optimized pattern)
			if (!($rowValue instanceof DateTime)) {
				$rowValue = new DateTime($rowValue);
			}

			if ($rowValue->getTimestamp() < $dateFrom->getTimestamp()) {
				continue;
			}

			if ($rowValue->getTimestamp() > $dateTo->getTimestamp()) {
				continue;
			}
		}
	}

	/**
	 * Original sort key extraction with string cast and DateTimeInterface check
	 *
	 * @param array{row_count: int} $params
	 */
	#[Bench\Revs(200)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUpDateRows')]
	#[Bench\ParamProviders('provideRowCounts')]
	public function benchSortWithGrouping(array $params): void
	{
		$data = [];

		foreach ($this->dateRows as $item) {
			$value = $item['name'];
			$sortBy = $value instanceof DateTimeInterface ? $value->format('Y-m-d H:i:s') : (string) $value;
			$data[$sortBy][] = $item;
		}

		ksort($data, SORT_LOCALE_STRING);

		$dataSource = [];

		foreach ($data as $i) {
			foreach ($i as $item) {
				$dataSource[] = $item;
			}
		}
	}

	/**
	 * Optimized sort with array_merge spread for flattening
	 *
	 * @param array{row_count: int} $params
	 */
	#[Bench\Revs(200)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUpDateRows')]
	#[Bench\ParamProviders('provideRowCounts')]
	public function benchSortWithArrayMerge(array $params): void
	{
		$data = [];

		foreach ($this->dateRows as $item) {
			$value = $item['name'];
			$sortBy = $value instanceof DateTimeInterface ? $value->format('Y-m-d H:i:s') : (string) $value;
			$data[$sortBy][] = $item;
		}

		ksort($data, SORT_LOCALE_STRING);

		$dataSource = $data !== [] ? array_merge(...array_values($data)) : [];
	}

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

	/**
	 * @param array{row_count: int} $params
	 */
	public function setUpSortData(array $params): void
	{
		$this->sortGroupedData = [];
		$names = ['Alice', 'Bob', 'Charlie', 'Diana', 'Eve', 'Frank'];

		for ($i = 0; $i < $params['row_count']; $i++) {
			$name = $names[$i % count($names)];
			$this->sortGroupedData[$name][] = ['id' => $i, 'name' => $name, 'age' => rand(18, 80)];
		}
	}

	/**
	 * @param array{row_count: int} $params
	 */
	public function setUpDateRows(array $params): void
	{
		$this->dateRows = [];

		for ($i = 0; $i < $params['row_count']; $i++) {
			$this->dateRows[] = [
				'id' => $i,
				'name' => 'Item ' . $i,
				'date' => '2024-' . str_pad((string) (($i % 12) + 1), 2, '0', STR_PAD_LEFT) . '-15',
			];
		}
	}

}
