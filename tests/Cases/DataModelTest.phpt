<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases;

use ArrayIterator;
use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\DataModel;
use Contributte\Datagrid\DataSource\IDataSource;
use Contributte\Datagrid\Exception\DatagridException;
use Contributte\Datagrid\Utils\Sorting;
use Generator;
use Mockery;
use RuntimeException;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

final class DataModelTest extends TestCase
{

	private array $events = [];

	public function testConsistentArrayContract(): void
	{
		$items = ['first' => ['id' => 1], 7 => ['id' => 2]];
		foreach ([false, true] as $subscribe) {
			foreach ([$items, new ArrayIterator($items)] as $data) {
				foreach ([false, true] as $singleRow) {
					$this->events = [];
					$source = Mockery::mock(IDataSource::class);
					$source->shouldReceive('filter', 'filterOne', 'sort')->andReturnSelf();
					$source->shouldReceive('getData')->once()->andReturn($data);
					$model = new DataModel($source, 'id');
					if ($subscribe) {
						$model->onDataLoaded[] = function (array $loaded): void {
							$this->events[] = $loaded;
						};
					}

					$result = $singleRow
						? $model->filterRow(['id' => 1])
						: $model->filterData(null, new Sorting([]), []);

					Assert::same($items, $result);
					Assert::same($subscribe ? [$items] : [], $this->events);
				}
			}
		}
	}

	public function testGeneratorIsConsumedOnceBeforeSubscribers(): void
	{
		$generator = (function (): Generator {
			foreach ([1, 2] as $id) {
				$this->events[] = 'fetch:' . $id;
				yield $id => ['id' => $id];
			}
		})();
		$source = Mockery::mock(IDataSource::class);
		$source->shouldReceive('filter')->once();
		$source->shouldReceive('sort')->once()->andReturnSelf();
		$source->shouldReceive('getData')->once()->andReturn($generator);
		$model = new DataModel($source, 'id');
		foreach ([1, 2] as $subscriber) {
			$model->onDataLoaded[] = function (array $items) use ($subscriber): void {
				$this->events[] = ['subscriber' => $subscriber, 'items' => $items];
			};
		}

		$result = $model->filterData(null, new Sorting([]), []);
		Assert::same([1 => ['id' => 1], 2 => ['id' => 2]], $result);
		Assert::same([
			'fetch:1',
			'fetch:2',
			['subscriber' => 1, 'items' => $result],
			['subscriber' => 2, 'items' => $result],
		], $this->events);
	}

	public function testFilterRowUsesReturnedSourceAndNotifiesForEmptyResults(): void
	{
		$filteredSource = Mockery::mock(IDataSource::class);
		$filteredSource->shouldReceive('getData')->once()->andReturn([]);
		$source = Mockery::mock(IDataSource::class);
		$source->shouldReceive('filterOne')->with(['id' => 99])->once()->andReturn($filteredSource);
		$source->shouldNotReceive('getData');
		$model = new DataModel($source, 'id');
		$model->onDataLoaded[] = function (array $items): void {
			$this->events[] = $items;
		};

		Assert::same([], $model->filterRow(['id' => 99]));
		Assert::same([[]], $this->events);
	}

	public function testFailedFetchDoesNotNotify(): void
	{
		$source = Mockery::mock(IDataSource::class);
		$source->shouldReceive('filter')->once();
		$source->shouldReceive('sort')->once()->andReturnSelf();
		$source->shouldReceive('getData')->once()->andThrow(RuntimeException::class, 'Fetch failed');
		$model = new DataModel($source, 'id');
		$model->onDataLoaded[] = static function (): void {
			Assert::fail('An unsuccessful fetch must not notify subscribers.');
		};

		Assert::exception(fn () => $model->filterData(null, new Sorting([]), []), RuntimeException::class, 'Fetch failed');
	}

	public function testGetDataModelRequiresDataSource(): void
	{
		$grid = new Datagrid();
		Assert::exception(fn () => $grid->getDataModel(), DatagridException::class, 'You have to set a data source first.');
		$grid->setDataSource([]);
		$model = $grid->getDataModel();
		Assert::same($grid->getDataSource(), $model->getDataSource());
		Assert::same($model, $grid->getDataModel());
		$grid->setDataSource([]);
		Assert::notSame($model, $grid->getDataModel());
	}

	protected function setUp(): void
	{
		$this->events = [];
	}

	protected function tearDown(): void
	{
		Mockery::close();
	}

}

(new DataModelTest())->run();
