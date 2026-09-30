<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases;

use ArrayObject;
use Contributte\Datagrid\AggregationFunction\FunctionSum;
use Contributte\Datagrid\AggregationFunction\IAggregationFunction;
use Contributte\Datagrid\AggregationFunction\ISingleColumnAggregationFunction;
use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\DataSource\ArrayDataSource;
use Contributte\Datagrid\Exception\DatagridException;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;
use Dibi\Fluent;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use Mockery;
use Nette\Application\UI\TemplateFactory;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Database\Table\Selection;
use Nextras\Orm\Collection\ICollection;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

final class AggregationFunctionTest extends TestCase
{

	private array $data = [
		['id' => 1, 'amount' => 10, 'status' => 'active'],
		['id' => 2, 'amount' => 20, 'status' => 'inactive'],
		['id' => 3, 'amount' => 30, 'status' => 'active'],
		['id' => 4, 'amount' => 40, 'status' => 'active'],
	];

	public function testArrayDataSourceSumsArraysAndObjects(): void
	{
		$rows = [
			['amount' => 1],
			new ArrayObject(['amount' => 2]),
			(object) ['amount' => 3],
		];
		$dataSource = new ArrayDataSource($rows);
		$sum = new FunctionSum('amount');

		$dataSource->processAggregation($sum);
		Assert::same(6, $sum->renderResult());

		// Processing again does not double count
		$dataSource->processAggregation($sum);
		Assert::same(6, $sum->renderResult());
	}

	public function testGridAggregationDataTypes(): void
	{
		$grid = $this->createGrid($this->data);
		$all = new FunctionSum('amount', IAggregationFunction::DATA_TYPE_ALL);
		$filtered = new FunctionSum('amount', IAggregationFunction::DATA_TYPE_FILTERED);
		$paginated = new FunctionSum('amount', IAggregationFunction::DATA_TYPE_PAGINATED);
		$grid->addAggregationFunction('id', $all);
		$grid->addAggregationFunction('amount', $filtered);
		$grid->addAggregationFunction('status', $paginated);

		$grid->addFilterSelect('status', 'Status', ['active' => 'Active']);
		$grid->setFilter(['status' => 'active']);
		$grid->sort = ['amount' => 'ASC'];
		$grid->setItemsPerPageList([2], false);
		$grid->perPage = 2;
		$grid->page = 2;

		$grid->render();

		Assert::same(100, $all->renderResult());
		Assert::same(80, $filtered->renderResult());
		Assert::same(40, $paginated->renderResult());
	}

	public function testGridAggregationWithObjectRows(): void
	{
		$rows = array_map(static fn (array $row): object => (object) $row, $this->data);
		$grid = $this->createGrid($rows);
		$sum = new FunctionSum('amount', IAggregationFunction::DATA_TYPE_ALL);
		$grid->addAggregationFunction('amount', $sum);

		$grid->render();

		Assert::same(100, $sum->renderResult());
	}

	public function testUnsupportedFunctionThrows(): void
	{
		$function = new class implements ISingleColumnAggregationFunction {

			public function getFilterDataType(): string
			{
				return IAggregationFunction::DATA_TYPE_ALL;
			}

			public function processDataSource(Fluent|QueryBuilder|Collection|Selection|ICollection $dataSource): void
			{
				// Not used with ArrayDataSource
			}

			public function renderResult(): mixed
			{
				return null;
			}

		};

		$dataSource = new ArrayDataSource($this->data);

		Assert::exception(
			static fn () => $dataSource->processAggregation($function),
			DatagridException::class,
			'%a% has to implement Contributte\Datagrid\AggregationFunction\IArrayAggregationFunction to work with ArrayDataSource'
		);
	}

	protected function tearDown(): void
	{
		Mockery::close();
	}

	private function createGrid(array $data): Datagrid
	{
		$grid = (new TestingDatagridFactory())->createTestingDatagrid();
		$grid->setRememberState(false);
		$grid->setDataSource($data);
		$grid->addColumnNumber('id', 'Id');
		$grid->addColumnNumber('amount', 'Amount')->setSortable();
		$grid->addColumnText('status', 'Status');

		$template = Mockery::mock(Template::class);
		$template->shouldReceive('setTranslator')->andReturnSelf();
		$template->shouldReceive('setFile')->andReturnSelf();
		$template->shouldReceive('render')->andReturnNull();

		$factory = Mockery::mock(TemplateFactory::class);
		$factory->shouldReceive('createTemplate')->andReturn($template);
		$grid->setTemplateFactory($factory);

		return $grid;
	}

}

(new AggregationFunctionTest())->run();
