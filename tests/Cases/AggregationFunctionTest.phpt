<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/TestingDatagridFactory.php';
require __DIR__ . '/../Files/AggregatableArrayDataSource.php';

use Contributte\Datagrid\AggregationFunction\FunctionSum;
use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Tests\Files\AggregatableArrayDataSource;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;
use Contributte\Datagrid\Utils\Sorting;
use Tester\Assert;
use Tester\TestCase;

final class AggregationFunctionTest extends TestCase
{

	private Datagrid $grid;

	private FunctionSum $sum;

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
		$this->sum = new FunctionSum('amount');
		$this->grid->addAggregationFunction('amount', $this->sum);
	}

	public function testPaginatedSumOfCurrentPage(): void
	{
		$this->grid->setItemsPerPageList([2]);
		$this->grid->perPage = 2;
		$this->grid->page = 2;

		$this->filterData();

		Assert::same(700, $this->sum->renderResult());
	}

	public function testPaginatedSumWithoutPagination(): void
	{
		$this->grid->setPagination(false);

		$this->filterData();

		Assert::same(1000, $this->sum->renderResult());
	}

	public function testPaginatedSumWithAllItemsPerPage(): void
	{
		$this->grid->setItemsPerPageList([2]);
		$this->grid->perPage = 'all';

		$this->filterData();

		Assert::same(1000, $this->sum->renderResult());
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
