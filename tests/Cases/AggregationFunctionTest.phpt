<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/TestingDatagridFactory.php';
require __DIR__ . '/../Files/AggregatableArrayDataSource.php';

use Contributte\Datagrid\AggregationFunction\FunctionSum;
use Contributte\Datagrid\AggregationFunction\IAggregationFunction;
use Contributte\Datagrid\AggregationFunction\IMultipleAggregationFunction;
use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Tests\Files\AggregatableArrayDataSource;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;
use Contributte\Datagrid\Utils\Sorting;
use Dibi\Fluent;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use Nette\Database\Table\Selection;
use Nextras\Orm\Collection\ICollection;
use Tester\Assert;
use Tester\TestCase;

final class AggregationFunctionTest extends TestCase
{

	private Datagrid $grid;

	private array $data = [
		['id' => 1, 'amount' => 100],
		['id' => 2, 'amount' => 200],
		['id' => 3, 'amount' => 300],
		['id' => 4, 'amount' => 400],
	];

	public function setUp(): void
	{
		$factory = new TestingDatagridFactory();
		$this->grid = $factory->createTestingDatagrid();
		$this->grid->addColumnNumber('amount', 'Amount');
		$this->grid->setDataSource(new AggregatableArrayDataSource($this->data));
	}

	public function testPaginatedSumOfCurrentPage(): void
	{
		$sum = $this->addSum();
		$this->grid->setItemsPerPageList([2]);
		$this->grid->perPage = 2;
		$this->grid->page = 2;

		$this->filterData();

		Assert::same(700, $sum->renderResult());
	}

	public function testPaginatedSumWithoutPagination(): void
	{
		$sum = $this->addSum();
		$this->grid->setPagination(false);

		$this->filterData();

		Assert::same(1000, $sum->renderResult());
	}

	public function testPaginatedSumWithAllItemsPerPage(): void
	{
		$sum = $this->addSum();
		$this->grid->setItemsPerPageList([2]);
		$this->grid->perPage = 'all';

		$this->filterData();

		Assert::same(1000, $sum->renderResult());
	}

	public function testRepeatedFilterDataDoesNotAccumulate(): void
	{
		$sum = $this->addSum();
		$this->grid->setPagination(false);

		$this->filterData();
		$this->filterData();

		Assert::same(1000, $sum->renderResult());
	}

	public function testFilteredSumWithoutPaginationRunsOnce(): void
	{
		$sum = new FunctionSum('amount', IAggregationFunction::DATA_TYPE_FILTERED);
		$this->grid->addAggregationFunction('amount', $sum);
		$this->grid->setPagination(false);

		$this->filterData();

		Assert::same(1000, $sum->renderResult());
	}

	public function testMultipleFunctionWithoutPagination(): void
	{
		$function = $this->createCountingFunction(IAggregationFunction::DATA_TYPE_PAGINATED);
		$this->grid->setMultipleAggregationFunction($function);
		$this->grid->setPagination(false);

		$this->filterData();

		Assert::same('1000', $function->renderResult('amount'));
		Assert::same('1', $function->renderResult('calls'));
	}

	public function testMultipleFunctionWithPagination(): void
	{
		$function = $this->createCountingFunction(IAggregationFunction::DATA_TYPE_PAGINATED);
		$this->grid->setMultipleAggregationFunction($function);
		$this->grid->setItemsPerPageList([2]);
		$this->grid->perPage = 2;

		$this->filterData();

		Assert::same('300', $function->renderResult('amount'));
		Assert::same('1', $function->renderResult('calls'));
	}

	private function addSum(): FunctionSum
	{
		$sum = new FunctionSum('amount');
		$this->grid->addAggregationFunction('amount', $sum);

		return $sum;
	}

	private function createCountingFunction(string $dataType): IMultipleAggregationFunction
	{
		return new class ($dataType) implements IMultipleAggregationFunction {

			private int $calls = 0;

			private int $amount = 0;

			public function __construct(private string $dataType)
			{
			}

			public function getFilterDataType(): string
			{
				return $this->dataType;
			}

			public function processDataSource(Fluent|QueryBuilder|Collection|Selection|ICollection $dataSource): void
			{
				$this->calls++;

				if ($dataSource instanceof Collection) {
					foreach ($dataSource as $item) {
						$this->amount += $item->amount;
					}
				}
			}

			public function renderResult(string $key): mixed
			{
				return (string) ($key === 'calls' ? $this->calls : $this->amount);
			}

		};
	}

	private function filterData(): void
	{
		$this->grid->getDataModel()->filterData(
			$this->grid->getPaginator(),
			new Sorting([]),
			[]
		);
	}

}

(new AggregationFunctionTest())->run();
