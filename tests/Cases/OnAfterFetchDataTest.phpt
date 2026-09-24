<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases;

use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\DataSource\IDataSource;
use Contributte\Datagrid\Row;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;
use Generator;
use Mockery;
use Nette\Application\UI\TemplateFactory;
use Nette\Bridges\ApplicationLatte\Template;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

final class OnAfterFetchDataTest extends TestCase
{

	private Datagrid $grid;

	private Template $template;

	private array $events = [];

	private array $amounts = [];

	private array $received = [];

	private array $data = [
		['id' => 1, 'amount' => 10, 'status' => 'active'],
		['id' => 2, 'amount' => 20, 'status' => 'inactive'],
		['id' => 3, 'amount' => 30, 'status' => 'active'],
		['id' => 4, 'amount' => 40, 'status' => 'active'],
	];

	public function testFilteredSortedPageBeforeRows(): void
	{
		$this->grid->addFilterSelect('status', 'Status', ['active' => 'Active']);
		$this->grid->setFilter(['status' => 'active']);
		$this->grid->sort = ['amount' => 'DESC'];
		$this->grid->setItemsPerPageList([2], false);
		$this->grid->perPage = 2;
		$this->grid->page = 2;

		$this->grid->onAfterFetchData[] = function (array $items): void {
			$this->events[] = array_column($items, 'id');
			$this->amounts = array_column($items, 'amount', 'id');
		};
		$summary = $this->grid->setColumnsSummary(['amount'], fn (array $item): int => $this->amounts[$item['id']]);
		$this->grid->setRowCallback(function (array $item): void {
			$this->events[] = $item['id'];
		});

		$this->grid->render();

		Assert::same([[1], 1], $this->events);
		Assert::same([1], $this->renderedIds());
		Assert::same('10', $summary->render('amount'));
	}

	public function testEmptyResultAndRepeatedRender(): void
	{
		$this->grid->onAfterFetchData[] = function (array $items): void {
			$this->events[] = $items;
		};
		$this->grid->setDataSource([]);
		$this->grid->render();
		$this->grid->render();

		Assert::same([[], []], $this->events);
		Assert::same([], $this->renderedIds());
	}

	public function testRedrawSingleRow(): void
	{
		$this->grid->onAfterFetchData[] = function (array $items): void {
			$this->events[] = array_column($items, 'id');
		};
		$this->grid->redrawItem(3);
		$this->grid->render();

		Assert::same([[3]], $this->events);
		Assert::same([3], $this->renderedIds());
	}

	public function testRedrawWithSummaryFetchesWholePage(): void
	{
		$this->grid->onAfterFetchData[] = function (array $items): void {
			$this->events[] = array_column($items, 'id');
		};
		$summary = $this->grid->setColumnsSummary(['amount']);
		$this->grid->setItemsPerPageList([2], false);
		$this->grid->perPage = 2;
		$this->grid->page = 2;
		$this->grid->redrawItem(3);
		$this->grid->render();

		Assert::same([[3, 4]], $this->events);
		Assert::same([3], $this->renderedIds());
		Assert::same('70', $summary->render('amount'));
	}

	public function testGeneratorIsSharedByCallbacksAndRendering(): void
	{
		$this->setIterableSource($this->generateItems());
		foreach ([1, 2] as $subscriber) {
			$this->grid->onAfterFetchData[] = function (array $items) use ($subscriber): void {
				$this->events[$subscriber] = array_column($items, 'id');
			};
		}

		$this->grid->render();

		Assert::same([1 => [1, 2, 3, 4], 2 => [1, 2, 3, 4]], $this->events);
		Assert::same([1, 2, 3, 4], $this->renderedIds());
	}

	public function testArrayKeysArePreserved(): void
	{
		$items = ['first' => $this->data[0], 'second' => $this->data[1]];
		$this->setIterableSource($items);
		$this->grid->onAfterFetchData[] = function (array $data): void {
			$this->received = $data;
		};
		$this->grid->render();

		Assert::same($items, $this->received);
		Assert::same([1, 2], $this->renderedIds());
	}

	public function testGeneratorRemainsLazyWithoutCallbacks(): void
	{
		$items = (function (): Generator {
			foreach ([1, 2] as $id) {
				$this->events[] = 'fetch:' . $id;
				yield ['id' => $id, 'amount' => $id];
			}
		})();
		$this->setIterableSource($items);
		$this->grid->setRowCallback(function (array $item): void {
			$this->events[] = 'row:' . $item['id'];
		});
		$this->grid->render();

		Assert::same(['fetch:1', 'row:1', 'fetch:2', 'row:2'], $this->events);
		Assert::same([1, 2], $this->renderedIds());
	}

	public function testExportsDoNotInvokeEvent(): void
	{
		$this->grid->onAfterFetchData[] = static function (): void {
			Assert::fail('Export must not invoke onAfterFetchData.');
		};
		$this->grid->addFilterSelect('status', 'Status', ['active' => 'Active']);
		$this->grid->setFilter(['status' => 'active']);
		foreach ([false, true] as $filtered) {
			$this->grid->addExportCallback('Export', function (array $items): void {
				$this->events[] = array_column($items, 'id');
			}, $filtered);
		}

		$this->grid->handleExport(1);
		$this->grid->handleExport(2);

		Assert::same([[1, 2, 3, 4], [1, 3, 4]], $this->events);
	}

	protected function setUp(): void
	{
		$this->events = [];
		$this->amounts = [];
		$this->received = [];
		$this->grid = (new TestingDatagridFactory())->createTestingDatagrid();
		$this->grid->setRememberState(false);
		$this->grid->setDataSource($this->data);
		$this->grid->addColumnNumber('amount', 'Amount')->setSortable();

		// Exercise the real render pipeline, replacing only the final template output.
		$template = Mockery::mock(Template::class);
		$template->shouldReceive('setTranslator')->andReturnSelf();
		$template->shouldReceive('setFile')->andReturnSelf();
		$template->shouldReceive('render')->andReturnNull();
		$this->template = $template;

		$factory = Mockery::mock(TemplateFactory::class);
		$factory->shouldReceive('createTemplate')->andReturn($template);
		$this->grid->setTemplateFactory($factory);
	}

	protected function tearDown(): void
	{
		Mockery::close();
	}

	private function setIterableSource(iterable $items): void
	{
		$source = Mockery::mock(IDataSource::class);
		$source->shouldReceive('filter')->once();
		$source->shouldReceive('sort')->once()->andReturnSelf();
		$source->shouldReceive('getData')->once()->andReturn($items);
		$this->grid->setPagination(false);
		$this->grid->setDataSource($source);
	}

	private function generateItems(): Generator
	{
		foreach ($this->data as $item) {
			// Repeated keys must not discard any rows when materializing a generator.
			yield 'same-key' => $item;
		}
	}

	private function renderedIds(): array
	{
		return array_map(static fn (Row $row): mixed => $row->getId(), $this->template->getParameters()['rows']);
	}

}

(new OnAfterFetchDataTest())->run();
