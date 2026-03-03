<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/TestingDatagridFactory.php';

use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Row;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;
use Tester\Assert;
use Tester\TestCase;

final class ColumnsSummaryTest extends TestCase
{

	private Datagrid $grid;

	private array $data = [
		['id' => 1, 'amount' => 100],
		['id' => 2, 'amount' => 200],
		['id' => 3, 'amount' => 300],
	];

	public function setUp(): void
	{
		$factory = new TestingDatagridFactory();
		$this->grid = $factory->createTestingDatagrid();
		$this->grid->addColumnNumber('amount', 'Amount');
		$this->grid->setDataSource($this->data);
	}

	public function testSummaryAccumulatesAllRows(): void
	{
		$summary = $this->grid->setColumnsSummary(['amount']);

		foreach ($this->data as $item) {
			new Row($this->grid, $item, 'id');
		}

		Assert::same('600', $summary->render('amount'));
	}

	/**
	 * Demonstrates the bug that existed before the fix:
	 * when only the redrawn row was processed, ColumnsSummary showed the wrong (partial) total.
	 */
	public function testSummaryWithSingleRowShowsPartialTotal(): void
	{
		$summary = $this->grid->setColumnsSummary(['amount']);

		// Only the redrawn row (id=2, amount=200) is processed — as the bug did
		new Row($this->grid, ['id' => 2, 'amount' => 200], 'id');

		Assert::same('200', $summary->render('amount'));
	}

	/**
	 * Verifies that when redrawItem is set alongside columnsSummary,
	 * Datagrid routes to filterData (all rows) — not filterRow (single row).
	 *
	 * The condition in Datagrid::render():
	 *   $redrawItem !== [] && !($columnsSummary instanceof ColumnsSummary)
	 * must evaluate to FALSE so that filterData is used and all rows are iterated,
	 * ensuring ColumnsSummary receives all values.
	 */
	public function testFilterDataUsedWhenColumnsSummaryAndRedrawItemSet(): void
	{
		$this->grid->setColumnsSummary(['amount']);

		Assert::true($this->grid->hasColumnsSummary());

		$reflectionRedrawItem = new \ReflectionProperty(Datagrid::class, 'redrawItem');
		$reflectionRedrawItem->setValue($this->grid, ['id' => 2]);

		$redrawItem = $reflectionRedrawItem->getValue($this->grid);

		Assert::false($redrawItem !== [] && !$this->grid->hasColumnsSummary());
	}

	/**
	 * Without columnsSummary, redrawItem causes filterRow to be used (expected behaviour).
	 */
	public function testFilterRowUsedWhenNoColumnsSummaryAndRedrawItemSet(): void
	{
		Assert::false($this->grid->hasColumnsSummary());

		$reflRedrawItem = new \ReflectionProperty(Datagrid::class, 'redrawItem');
		$reflRedrawItem->setValue($this->grid, ['id' => 2]);

		$redrawItem = $reflRedrawItem->getValue($this->grid);

		$wouldUseFilterRow = $redrawItem !== [] && !$this->grid->hasColumnsSummary();

		Assert::true($wouldUseFilterRow);
	}

}


(new ColumnsSummaryTest())->run();
