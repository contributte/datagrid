<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Benchmarks;

use PhpBench\Attributes as Bench;

/**
 * Benchmarks for CSV export and ColumnsSummary core operations:
 * - CSV row generation: strip_tags on rendered HTML output at scale
 * - Column value extraction: iterating rows and reading column values into arrays
 * - ColumnsSummary aggregation: summing numeric column values across rows
 * - CSV line serialization: fputcsv overhead for row output
 */
class CsvExportBench
{

	/** @var array<int, array<string, mixed>> */
	private array $rows = [];

	/** @var array<int, string> */
	private array $htmlStrings = [];

	/** @var array<string, int|float> */
	private array $summaryAccumulator = [];

	/**
	 * Benchmark strip_tags on HTML strings — the main per-cell cost in CsvDataModel::getRow()
	 *
	 * @param array{row_count: int} $params
	 */
	#[Bench\Revs(100)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUpHtmlStrings')]
	#[Bench\ParamProviders('provideRowCounts')]
	public function benchStripTagsPerCell(array $params): void
	{
		$columns = ['name', 'email', 'status', 'amount'];

		foreach ($this->rows as $row) {
			$csvRow = [];

			foreach ($columns as $col) {
				$csvRow[] = strip_tags((string) $row[$col]);
			}
		}
	}

	/**
	 * Benchmark strip_tags on pre-rendered HTML with nested tags — worst-case scenario
	 *
	 * @param array{row_count: int} $params
	 */
	#[Bench\Revs(100)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUpHtmlStrings')]
	#[Bench\ParamProviders('provideRowCounts')]
	public function benchStripTagsComplexHtml(array $params): void
	{
		foreach ($this->htmlStrings as $html) {
			strip_tags($html);
		}
	}

	/**
	 * Benchmark column value extraction — iterating rows and building output arrays
	 * as done in CsvDataModel::getSimpleData()
	 *
	 * @param array{row_count: int} $params
	 */
	#[Bench\Revs(100)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUpRows')]
	#[Bench\ParamProviders('provideRowCounts')]
	public function benchColumnValueExtraction(array $params): void
	{
		$columns = ['name', 'email', 'status', 'amount'];
		$result = [];

		// Header row
		$result[] = $columns;

		// Data rows — mirrors CsvDataModel::getSimpleData()
		foreach ($this->rows as $item) {
			$row = [];

			foreach ($columns as $col) {
				$row[] = (string) $item[$col];
			}

			$result[] = $row;
		}
	}

	/**
	 * Benchmark ColumnsSummary::add() — summing numeric values across rows
	 *
	 * @param array{row_count: int} $params
	 */
	#[Bench\Revs(100)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUpRows')]
	#[Bench\ParamProviders('provideRowCounts')]
	public function benchColumnsSummaryAdd(array $params): void
	{
		$summaryColumns = ['amount', 'quantity'];
		$summary = array_fill_keys($summaryColumns, 0);

		foreach ($this->rows as $row) {
			foreach ($summaryColumns as $key) {
				$summary[$key] += $row[$key];
			}
		}
	}

	/**
	 * Benchmark ColumnsSummary::add() with callback — custom value extraction per row
	 *
	 * @param array{row_count: int} $params
	 */
	#[Bench\Revs(100)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUpRows')]
	#[Bench\ParamProviders('provideRowCounts')]
	public function benchColumnsSummaryAddWithCallback(array $params): void
	{
		$summaryColumns = ['amount', 'quantity'];
		$summary = array_fill_keys($summaryColumns, 0);
		$callback = static fn (array $item, string $column): int|float => $item[$column] * 1;

		foreach ($this->rows as $row) {
			foreach ($summaryColumns as $key) {
				$summary[$key] += $callback($row, $key);
			}
		}
	}

	/**
	 * Benchmark ColumnsSummary::render() — number_format on accumulated totals
	 *
	 * @param array{row_count: int} $params
	 */
	#[Bench\Revs(100)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUpSummaryAccumulator')]
	#[Bench\ParamProviders('provideRowCounts')]
	public function benchColumnsSummaryRender(array $params): void
	{
		$formats = [
			'amount' => [2, '.', ' '],
			'quantity' => [0, '.', ' '],
		];

		foreach ($this->summaryAccumulator as $key => $value) {
			number_format(
				(float) $value,
				$formats[$key][0],
				$formats[$key][1],
				$formats[$key][2]
			);
		}
	}

	/**
	 * Benchmark CSV line serialization via fputcsv — mirrors CsvResponse::printCsv()
	 *
	 * @param array{row_count: int} $params
	 */
	#[Bench\Revs(100)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUpRows')]
	#[Bench\ParamProviders('provideRowCounts')]
	public function benchFputcsvSerialization(array $params): void
	{
		$columns = ['name', 'email', 'status', 'amount'];
		$delimiter = ';';

		foreach ($this->rows as $item) {
			$row = [];

			foreach ($columns as $col) {
				$row[] = strip_tags((string) $item[$col]);
			}

			$out = fopen('php://memory', 'wb+');
			fputcsv($out, $row, $delimiter, escape: '\\');
			rewind($out);
			$line = stream_get_contents($out);
			fclose($out);
		}
	}

	/**
	 * Benchmark full CSV export pipeline — extract, strip_tags, serialize, encode
	 * Combines all steps as they happen in a real export
	 *
	 * @param array{row_count: int} $params
	 */
	#[Bench\Revs(100)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUpHtmlStrings')]
	#[Bench\ParamProviders('provideRowCounts')]
	public function benchFullCsvRowPipeline(array $params): void
	{
		$columns = ['name', 'email', 'status', 'amount'];
		$delimiter = ';';
		$output = '';

		foreach ($this->rows as $item) {
			$row = [];

			foreach ($columns as $col) {
				$row[] = strip_tags((string) $item[$col]);
			}

			$out = fopen('php://memory', 'wb+');
			fputcsv($out, $row, $delimiter, escape: '\\');
			rewind($out);
			$output .= stream_get_contents($out);
			fclose($out);
		}
	}

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
	 * @param array{row_count: int} $params
	 */
	public function setUpRows(array $params): void
	{
		$this->rows = [];
		$statuses = ['active', 'inactive', 'pending', 'archived'];

		for ($i = 0; $i < $params['row_count']; $i++) {
			$this->rows[] = [
				'id' => $i,
				'name' => 'User ' . $i,
				'email' => 'user' . $i . '@example.com',
				'status' => $statuses[$i % count($statuses)],
				'amount' => round($i * 1.5 + 10.99, 2),
				'quantity' => $i % 50 + 1,
			];
		}
	}

	/**
	 * @param array{row_count: int} $params
	 */
	public function setUpHtmlStrings(array $params): void
	{
		$this->setUpRows($params);

		$this->htmlStrings = [];
		$templates = [
			'<span class="text-success"><i class="fa fa-check"></i> Active</span>',
			'<a href="/users/%d" class="btn btn-sm btn-primary"><strong>User %d</strong></a>',
			'<td class="col-amount"><span class="badge badge-info">$%s</span></td>',
			'<div class="status-wrapper"><span class="label label-warning">Pending</span> <small>(since 2024)</small></div>',
		];

		for ($i = 0; $i < $params['row_count']; $i++) {
			$this->htmlStrings[] = sprintf($templates[$i % count($templates)], $i, $i, number_format($i * 1.5, 2));
		}

		// Also set HTML into the row values to simulate rendered column output
		foreach ($this->rows as $idx => &$row) {
			$row['name'] = sprintf('<a href="/user/%d"><strong>%s</strong></a>', $idx, $row['name']);
			$row['status'] = sprintf('<span class="badge badge-%s">%s</span>', $row['status'], $row['status']);
		}

		unset($row);
	}

	/**
	 * @param array{row_count: int} $params
	 */
	public function setUpSummaryAccumulator(array $params): void
	{
		$this->summaryAccumulator = [
			'amount' => 0,
			'quantity' => 0,
		];

		for ($i = 0; $i < $params['row_count']; $i++) {
			$this->summaryAccumulator['amount'] += round($i * 1.5 + 10.99, 2);
			$this->summaryAccumulator['quantity'] += $i % 50 + 1;
		}
	}

}
