<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases;

require __DIR__ . '/../bootstrap.php';

use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactoryRouter;
use Nette\Application\AbortException;
use stdClass;
use Tester\Assert;
use Tester\TestCase;

final class DatagridTest extends TestCase
{

	public function testDefaultFilter(): void
	{
		$factory = new TestingDatagridFactoryRouter();
		/** @var Datagrid $grid */
		$grid = $factory->createTestingDatagrid()->getComponent('grid');
		$grid->addFilterText('test', 'Test filter');
		$grid->setDefaultFilter([
			'test' => 'value',
		]);

		$grid->setFilter(['test' => 'value']);
		Assert::true($grid->isFilterDefault());

		$grid->setFilter(['test' => null]);
		Assert::false($grid->isFilterDefault());
	}

	public function testResetFilterLinkWithRememberOption(): void
	{
		$factory = new TestingDatagridFactoryRouter();
		/** @var Datagrid $grid */
		$grid = $factory->createTestingDatagrid()->getComponent('grid');
		$grid->setRememberState(true);

		Assert::exception(function () use ($grid): void {
			$grid->handleResetFilter();
		}, AbortException::class);
	}

	public function testResetFilterLinkWithNoRememberOption(): void
	{
		$factory = new TestingDatagridFactoryRouter();
		/** @var Datagrid $grid */
		$grid = $factory->createTestingDatagrid()->getComponent('grid');
		$grid->setRememberState(false);

		Assert::exception(function () use ($grid): void {
			$grid->handleResetFilter();
		}, AbortException::class);
	}

	public function testSortCallbackOfPreviouslySortedColumnIsNotApplied(): void
	{
		$factory = new TestingDatagridFactoryRouter();
		/** @var Datagrid $grid */
		$grid = $factory->createTestingDatagrid()->getComponent('grid');
		$grid->setRememberState(true);
		$grid->setDataSource([
			['id' => 1, 'a' => 'x', 'b' => 2],
			['id' => 2, 'a' => 'y', 'b' => 1],
		]);

		$result = new stdClass();
		$result->sortCallbackCalled = false;
		$grid->addColumnText('a', 'A')->setSortable()->setSortableCallback(function () use ($result): array {
			$result->sortCallbackCalled = true;

			return [];
		});
		$grid->addColumnText('b', 'B')->setSortable();

		$grid->addExportCallback('Export', function (array $rows) use ($result): void {
			$result->exportedIds = array_column($rows, 'id');
		}, true);

		Assert::exception(fn () => $grid->handleSort(['a' => 'ASC']), AbortException::class);
		Assert::exception(fn () => $grid->handleSort(['b' => 'ASC']), AbortException::class);

		$grid->handleExport(1);

		Assert::false($result->sortCallbackCalled);
		Assert::same([2, 1], $result->exportedIds);
	}

}

(new DatagridTest())->run();
