<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Benchmarks;

use Contributte\Datagrid\Column\ColumnDateTime;
use Contributte\Datagrid\Column\ColumnNumber;
use Contributte\Datagrid\Column\ColumnText;
use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Row;
use Mockery;
use Nette\Utils\Html;
use PhpBench\Attributes as Bench;

/**
 * Benchmarks for Column rendering and HTML element generation:
 * - Element cache: getElementPrototype() caches Html instances per tag
 * - Column creation overhead for different column types (Text, Number, DateTime)
 * - Html::el() creation vs cloning (the pattern used in getElementForRender)
 * - Callback renderer vs replacement vs plain column value rendering
 */
class ColumnRenderBench
{

	/** @var ColumnText[] */
	private array $textColumns = [];

	/** @var ColumnNumber[] */
	private array $numberColumns = [];

	/** @var ColumnDateTime[] */
	private array $dateTimeColumns = [];

	/** @var Html[] */
	private array $htmlPrototypes = [];

	/** @var ColumnText[] */
	private array $rendererColumns = [];

	/** @var ColumnText[] */
	private array $replacementColumns = [];

	/** @var ColumnText[] */
	private array $plainColumns = [];

	/** @var Row[] */
	private array $rows = [];

	// ---------------------------------------------------------------
	// 1. Element cache benchmarks
	// ---------------------------------------------------------------

	/**
	 * Repeated getElementPrototype('td') calls -- cache hit path.
	 * The first call creates the Html element, subsequent calls return the cached instance.
	 *
	 * @param array{columns: int} $params
	 */
	#[Bench\Revs(1000)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUpTextColumns')]
	#[Bench\ParamProviders('provideColumnCounts')]
	public function benchElementCacheHit(array $params): void
	{
		for ($i = 0; $i < $params['columns']; $i++) {
			$column = $this->textColumns[$i];

			// First call populates the cache
			$column->getElementPrototype('td');

			// Subsequent calls should hit the cache
			$column->getElementPrototype('td');
			$column->getElementPrototype('td');
		}
	}

	/**
	 * getElementPrototype() with alternating td/th tags -- both cache slots.
	 *
	 * @param array{columns: int} $params
	 */
	#[Bench\Revs(1000)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUpTextColumns')]
	#[Bench\ParamProviders('provideColumnCounts')]
	public function benchElementCacheBothTags(array $params): void
	{
		for ($i = 0; $i < $params['columns']; $i++) {
			$column = $this->textColumns[$i];
			$column->getElementPrototype('td');
			$column->getElementPrototype('th');
		}
	}

	// ---------------------------------------------------------------
	// 2. Raw Html::el() creation and cloning
	// ---------------------------------------------------------------

	/**
	 * Baseline: creating Html::el('td') fresh every time (no cache).
	 *
	 * @param array{columns: int} $params
	 */
	#[Bench\Revs(1000)]
	#[Bench\Iterations(10)]
	#[Bench\ParamProviders('provideColumnCounts')]
	public function benchHtmlElCreationFresh(array $params): void
	{
		for ($i = 0; $i < $params['columns']; $i++) {
			$td = Html::el('td');
			$th = Html::el('th');
		}
	}

	/**
	 * Cloning a cached Html prototype (the pattern used inside getElementForRender).
	 *
	 * @param array{columns: int} $params
	 */
	#[Bench\Revs(1000)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUpHtmlPrototypes')]
	#[Bench\ParamProviders('provideColumnCounts')]
	public function benchHtmlElCloneFromCache(array $params): void
	{
		$tdProto = $this->htmlPrototypes['td'];
		$thProto = $this->htmlPrototypes['th'];

		for ($i = 0; $i < $params['columns']; $i++) {
			$td = clone $tdProto;
			$th = clone $thProto;
		}
	}

	/**
	 * Clone + appendAttribute (simulates the full getElementForRender flow without Datagrid dependency).
	 *
	 * @param array{columns: int} $params
	 */
	#[Bench\Revs(1000)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUpHtmlPrototypes')]
	#[Bench\ParamProviders('provideColumnCounts')]
	public function benchHtmlElCloneWithAttributes(array $params): void
	{
		$tdProto = $this->htmlPrototypes['td'];

		for ($i = 0; $i < $params['columns']; $i++) {
			$el = clone $tdProto;
			$el->appendAttribute('class', sprintf('text-%s', 'start'));
			$el->appendAttribute('class', sprintf('col-%s', 'column_' . $i));
		}
	}

	/**
	 * Fresh Html::el() + appendAttribute (no cache, no clone).
	 *
	 * @param array{columns: int} $params
	 */
	#[Bench\Revs(1000)]
	#[Bench\Iterations(10)]
	#[Bench\ParamProviders('provideColumnCounts')]
	public function benchHtmlElFreshWithAttributes(array $params): void
	{
		for ($i = 0; $i < $params['columns']; $i++) {
			$el = Html::el('td');
			$el->appendAttribute('class', sprintf('text-%s', 'start'));
			$el->appendAttribute('class', sprintf('col-%s', 'column_' . $i));
		}
	}

	// ---------------------------------------------------------------
	// 3. Column creation overhead
	// ---------------------------------------------------------------

	/**
	 * ColumnText creation overhead.
	 *
	 * @param array{columns: int} $params
	 */
	#[Bench\Revs(500)]
	#[Bench\Iterations(10)]
	#[Bench\ParamProviders('provideColumnCounts')]
	public function benchColumnTextCreation(array $params): void
	{
		$grid = $this->createGridMock();

		for ($i = 0; $i < $params['columns']; $i++) {
			new ColumnText($grid, 'col_' . $i, 'column_' . $i, 'Column ' . $i);
		}

		Mockery::close();
	}

	/**
	 * ColumnNumber creation overhead.
	 *
	 * @param array{columns: int} $params
	 */
	#[Bench\Revs(500)]
	#[Bench\Iterations(10)]
	#[Bench\ParamProviders('provideColumnCounts')]
	public function benchColumnNumberCreation(array $params): void
	{
		$grid = $this->createGridMock();

		for ($i = 0; $i < $params['columns']; $i++) {
			new ColumnNumber($grid, 'col_' . $i, 'column_' . $i, 'Column ' . $i);
		}

		Mockery::close();
	}

	/**
	 * ColumnDateTime creation overhead.
	 *
	 * @param array{columns: int} $params
	 */
	#[Bench\Revs(500)]
	#[Bench\Iterations(10)]
	#[Bench\ParamProviders('provideColumnCounts')]
	public function benchColumnDateTimeCreation(array $params): void
	{
		$grid = $this->createGridMock();

		for ($i = 0; $i < $params['columns']; $i++) {
			new ColumnDateTime($grid, 'col_' . $i, 'column_' . $i, 'Column ' . $i);
		}

		Mockery::close();
	}

	/**
	 * Mixed column type creation (simulates a real datagrid with diverse column types).
	 *
	 * @param array{columns: int} $params
	 */
	#[Bench\Revs(500)]
	#[Bench\Iterations(10)]
	#[Bench\ParamProviders('provideColumnCounts')]
	public function benchMixedColumnCreation(array $params): void
	{
		$grid = $this->createGridMock();

		for ($i = 0; $i < $params['columns']; $i++) {
			match ($i % 3) {
				0 => new ColumnText($grid, 'col_' . $i, 'column_' . $i, 'Column ' . $i),
				1 => new ColumnNumber($grid, 'col_' . $i, 'column_' . $i, 'Column ' . $i),
				2 => new ColumnDateTime($grid, 'col_' . $i, 'column_' . $i, 'Column ' . $i),
			};
		}

		Mockery::close();
	}

	// ---------------------------------------------------------------
	// 4. Cell attributes
	// ---------------------------------------------------------------

	/**
	 * addCellAttributes applies attributes to both td and th prototypes.
	 *
	 * @param array{columns: int} $params
	 */
	#[Bench\Revs(500)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUpTextColumns')]
	#[Bench\ParamProviders('provideColumnCounts')]
	public function benchAddCellAttributes(array $params): void
	{
		for ($i = 0; $i < $params['columns']; $i++) {
			$this->textColumns[$i]->addCellAttributes([
				'class' => 'datagrid-fit-content',
				'data-column' => 'col_' . $i,
			]);
		}
	}

	// ---------------------------------------------------------------
	// 5. Callback renderer vs replacement vs plain value rendering
	// ---------------------------------------------------------------

	/**
	 * Column::render() with a custom callback renderer set via setRenderer().
	 * Exercises the useRenderer() -> call_user_func_array() path.
	 *
	 * @param array{columns: int} $params
	 */
	#[Bench\Revs(500)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUpRenderingColumns')]
	#[Bench\ParamProviders('provideColumnCounts')]
	public function benchRenderWithCallback(array $params): void
	{
		$count = min($params['columns'], count($this->rendererColumns));

		for ($i = 0; $i < $count; $i++) {
			$this->rendererColumns[$i]->render($this->rows[$i]);
		}
	}

	/**
	 * Column::render() with replacements set via setReplacement().
	 * Exercises the applyReplacements() path.
	 *
	 * @param array{columns: int} $params
	 */
	#[Bench\Revs(500)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUpRenderingColumns')]
	#[Bench\ParamProviders('provideColumnCounts')]
	public function benchRenderWithReplacement(array $params): void
	{
		$count = min($params['columns'], count($this->replacementColumns));

		for ($i = 0; $i < $count; $i++) {
			$this->replacementColumns[$i]->render($this->rows[$i]);
		}
	}

	/**
	 * Column::render() with no renderer and no replacements -- plain getColumnValue().
	 * Exercises the simplest path through render().
	 *
	 * @param array{columns: int} $params
	 */
	#[Bench\Revs(500)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUpRenderingColumns')]
	#[Bench\ParamProviders('provideColumnCounts')]
	public function benchRenderPlainValue(array $params): void
	{
		$count = min($params['columns'], count($this->plainColumns));

		for ($i = 0; $i < $count; $i++) {
			$this->plainColumns[$i]->render($this->rows[$i]);
		}
	}

	/**
	 * Column::render() with a conditional renderer (condition callback returns true).
	 * Tests the overhead of the condition check before the renderer callback.
	 *
	 * @param array{columns: int} $params
	 */
	#[Bench\Revs(500)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUpConditionalRendererColumns')]
	#[Bench\ParamProviders('provideColumnCounts')]
	public function benchRenderWithConditionalCallback(array $params): void
	{
		$count = min($params['columns'], count($this->rendererColumns));

		for ($i = 0; $i < $count; $i++) {
			$this->rendererColumns[$i]->render($this->rows[$i]);
		}
	}

	// ---------------------------------------------------------------
	// Param providers
	// ---------------------------------------------------------------

	/**
	 * @return array<string, array{columns: int}>
	 */
	public function provideColumnCounts(): array
	{
		return [
			'8 columns' => ['columns' => 8],
			'20 columns' => ['columns' => 20],
			'50 columns' => ['columns' => 50],
		];
	}

	// ---------------------------------------------------------------
	// Setup methods
	// ---------------------------------------------------------------

	/**
	 * @param array{columns: int} $params
	 */
	public function setUpTextColumns(array $params): void
	{
		$this->textColumns = [];
		$grid = $this->createGridMock();

		for ($i = 0; $i < $params['columns']; $i++) {
			$this->textColumns[$i] = new ColumnText($grid, 'col_' . $i, 'column_' . $i, 'Column ' . $i);
		}
	}

	/**
	 * @param array{columns: int} $params
	 */
	public function setUpNumberColumns(array $params): void
	{
		$this->numberColumns = [];
		$grid = $this->createGridMock();

		for ($i = 0; $i < $params['columns']; $i++) {
			$this->numberColumns[$i] = new ColumnNumber($grid, 'col_' . $i, 'column_' . $i, 'Column ' . $i);
		}
	}

	/**
	 * @param array{columns: int} $params
	 */
	public function setUpDateTimeColumns(array $params): void
	{
		$this->dateTimeColumns = [];
		$grid = $this->createGridMock();

		for ($i = 0; $i < $params['columns']; $i++) {
			$this->dateTimeColumns[$i] = new ColumnDateTime($grid, 'col_' . $i, 'column_' . $i, 'Column ' . $i);
		}
	}

	public function setUpHtmlPrototypes(): void
	{
		$this->htmlPrototypes = [
			'td' => Html::el('td'),
			'th' => Html::el('th'),
		];
	}

	/**
	 * Set up three groups of columns for render-path benchmarks:
	 * - rendererColumns: have a custom callback renderer set
	 * - replacementColumns: have a replacement map set
	 * - plainColumns: no renderer, no replacements (raw value path)
	 *
	 * Also creates matching Row objects backed by simple arrays.
	 *
	 * @param array{columns: int} $params
	 */
	public function setUpRenderingColumns(array $params): void
	{
		$this->rendererColumns = [];
		$this->replacementColumns = [];
		$this->plainColumns = [];
		$this->rows = [];

		$grid = $this->createRowCompatibleGridMock();
		$statuses = ['active', 'inactive', 'pending'];

		for ($i = 0; $i < $params['columns']; $i++) {
			$item = [
				'id' => $i + 1,
				'name' => 'Item ' . $i,
				'status' => $statuses[$i % 3],
			];

			$this->rows[$i] = new Row($grid, $item, 'id');

			// Columns with callback renderer
			$col = new ColumnText($grid, 'name_r_' . $i, 'name', 'Name');
			$col->setRenderer(fn (array $item): string => strtoupper((string) $item['name']));
			$this->rendererColumns[$i] = $col;

			// Columns with replacement map
			$col = new ColumnText($grid, 'status_' . $i, 'status', 'Status');
			$col->setReplacement([
				'active' => 'Active',
				'inactive' => 'Inactive',
				'pending' => 'Pending',
			]);
			$this->replacementColumns[$i] = $col;

			// Plain columns (no renderer, no replacements)
			$this->plainColumns[$i] = new ColumnText($grid, 'name_p_' . $i, 'name', 'Name');
		}
	}

	/**
	 * Set up columns with conditional renderers (condition callback + renderer callback).
	 *
	 * @param array{columns: int} $params
	 */
	public function setUpConditionalRendererColumns(array $params): void
	{
		$this->rendererColumns = [];
		$this->rows = [];

		$grid = $this->createRowCompatibleGridMock();

		for ($i = 0; $i < $params['columns']; $i++) {
			$item = [
				'id' => $i + 1,
				'name' => 'Item ' . $i,
			];

			$this->rows[$i] = new Row($grid, $item, 'id');

			$col = new ColumnText($grid, 'name_cr_' . $i, 'name', 'Name');
			$col->setRendererOnCondition(
				fn (array $item): string => strtoupper((string) $item['name']),
				fn (array $item): bool => true,
			);
			$this->rendererColumns[$i] = $col;
		}
	}

	// ---------------------------------------------------------------
	// Mock helpers
	// ---------------------------------------------------------------

	private function createGridMock(): Datagrid
	{
		/** @var Datagrid $grid */
		$grid = Mockery::mock(Datagrid::class);

		return $grid;
	}

	/**
	 * Create a Datagrid mock that supports Row construction.
	 * Row::__construct() calls $datagrid->getColumnsSummary() and $datagrid->getColumnCallback().
	 */
	private function createRowCompatibleGridMock(): Datagrid
	{
		/** @var Datagrid&\Mockery\MockInterface $grid */
		$grid = Mockery::mock(Datagrid::class);
		$grid->shouldReceive('getColumnsSummary')->andReturn(null);

		return $grid;
	}

}
