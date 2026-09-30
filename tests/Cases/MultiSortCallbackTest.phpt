<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases;

use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Exception\DatagridArrayDataSourceException;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;
use Contributte\Datagrid\Utils\Sorting;
use ReflectionMethod;
use stdClass;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/TestingDatagridFactory.php';

final class MultiSortCallbackTest extends TestCase
{

	private Datagrid $grid;

	private array $calls = [];

	private array $data = [
		['id' => 1, 'a' => 2, 'b' => 1],
		['id' => 2, 'a' => 1, 'b' => 1],
		['id' => 3, 'a' => 1, 'b' => 2],
	];

	public function setUp(): void
	{
		$factory = new TestingDatagridFactory();
		$this->grid = $factory->createTestingDatagrid();
		$this->grid->setDataSource($this->data);
		$this->grid->setMultiSortEnabled();
		$this->calls = [];
	}

	public function testAllColumnCallbacksAreCalled(): void
	{
		$this->grid->addColumnText('a', 'A')
			->setSortable()
			->setSortableCallback(function (array $data, array $sort): array {
				$this->calls[] = ['a', $sort, array_column($data, 'id')];

				// Filters rows so the next callback proves it received this result (chaining probe)
				return array_values(array_filter($data, static fn (array $row): bool => $row['a'] === 1));
			});

		$bCallback = function (array $data, array $sort): array {
			$this->calls[] = ['b', $sort, array_column($data, 'id')];
			usort($data, static fn (array $x, array $y): int => $y['b'] <=> $x['b']);

			return $data;
		};

		$this->grid->addColumnText('b', 'B')
			->setSortable()
			->setSortableCallback($bCallback);

		$sort = ['a' => 'ASC', 'b' => 'DESC'];

		// Datagrid::loadState() / handleSort() pass the callback of the last sorted column
		$items = $this->filterData($sort, $bCallback);

		Assert::same([
			['a', $sort, [1, 2, 3]],
			['b', $sort, [2, 3]],
		], $this->calls);
		Assert::same([3, 2], array_column($items, 'id'));
	}

	public function testAllColumnCallbacksAreCalledWithoutSortCallback(): void
	{
		$this->grid->addColumnText('a', 'A')
			->setSortable()
			->setSortableCallback(function (array $data, array $sort): array {
				$this->calls[] = 'a';

				// Filters rows so the next callback proves it received this result (chaining probe)
				return array_values(array_filter($data, static fn (array $row): bool => $row['a'] === 1));
			});
		$this->grid->addColumnText('b', 'B')
			->setSortable()
			->setSortableCallback(function (array $data, array $sort): array {
				$this->calls[] = 'b';

				return array_reverse($data);
			});

		// Datagrid::setDefaultSort() on first render - no sort callback is passed
		$items = $this->filterData(['a' => 'ASC', 'b' => 'DESC'], null);

		Assert::same(['a', 'b'], $this->calls);
		Assert::same([3, 2], array_column($items, 'id'));
	}

	public function testNonDataSourceResultIsNotPassedOn(): void
	{
		$source = new stdClass();

		$this->grid->addColumnText('a', 'A')
			->setSortable()
			->setSortableCallback(function (stdClass $qb, array $sort): bool {
				$this->calls[] = ['a', $qb];

				return true;
			});
		$this->grid->addColumnText('b', 'B')
			->setSortable()
			->setSortableCallback(function (stdClass $qb, array $sort): void {
				$this->calls[] = ['b', $qb];
			});

		$sorting = $this->createSorting(['a' => 'ASC', 'b' => 'DESC'], null);
		$callback = $sorting->getSortCallback();
		Assert::notNull($callback);
		$callback($source, $sorting->getSort());

		Assert::same([['a', $source], ['b', $source]], $this->calls);
	}

	public function testNonArrayResultForArraySourceThrows(): void
	{
		$this->grid->addColumnText('a', 'A')
			->setSortable()
			->setSortableCallback(function (array $data, array $sort): bool {
				$this->calls[] = 'a';

				return true;
			});
		$this->grid->addColumnText('b', 'B')
			->setSortable()
			->setSortableCallback(function (array $data, array $sort): array {
				$this->calls[] = 'b';

				return $data;
			});

		Assert::exception(
			fn () => $this->filterData(['a' => 'ASC', 'b' => 'DESC'], null),
			DatagridArrayDataSourceException::class,
			'Sorting callback has to return array'
		);
		Assert::same(['a'], $this->calls);
	}

	public function testSingleColumnCallback(): void
	{
		$callback = function (array $data, array $sort): array {
			$this->calls[] = $sort;

			return array_reverse($data);
		};

		$this->grid->addColumnText('a', 'A')
			->setSortable()
			->setSortableCallback($callback);
		$this->grid->addColumnText('b', 'B')
			->setSortable();

		$items = $this->filterData(['a' => 'ASC'], $callback);

		Assert::same([['a' => 'ASC']], $this->calls);
		Assert::same([3, 2, 1], array_column($items, 'id'));
	}

	private function filterData(array $sort, ?callable $sortCallback): array
	{
		return $this->grid->getDataModel()->filterData(null, $this->createSorting($sort, $sortCallback), []);
	}

	private function createSorting(array $sort, ?callable $sortCallback): Sorting
	{
		$method = new ReflectionMethod(Datagrid::class, 'createSorting');
		$sorting = $method->invoke($this->grid, $sort, $sortCallback);
		Assert::type(Sorting::class, $sorting);

		return $sorting;
	}

}


$test_case = new MultiSortCallbackTest();
$test_case->run();
