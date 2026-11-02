<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases;

require __DIR__ . '/../bootstrap.php';

use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactoryRouter;
use Nette\Application\AbortException;
use Nette\Utils\ArrayHash;
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

	public function testFilterActiveWhenDefaultFilterCleared(): void
	{
		$factory = new TestingDatagridFactoryRouter();
		/** @var Datagrid $grid */
		$grid = $factory->createTestingDatagrid()->getComponent('grid');
		$grid->addFilterText('test', 'Test filter');
		$grid->addFilterRange('range', 'Range filter');

		// No default filter, empty filter -> nothing to reset
		$grid->setFilter([]);
		Assert::false($grid->isFilterActive());

		$grid->setDefaultFilter(['test' => 'value']);

		// Default filter cleared by user -> reset button must stay visible
		$grid->setFilter([]);
		Assert::true($grid->isFilterActive());

		$grid->setFilter(['test' => '']);
		Assert::true($grid->isFilterActive());

		// Session-shaped cleared filter (all keys present, empty values)
		$grid->setFilter(['test' => '', 'range' => ArrayHash::from(['from' => '', 'to' => ''])]);
		Assert::true($grid->isFilterActive());

		// Range default filter cleared
		$grid->setDefaultFilter(['range' => ['from' => 1, 'to' => 5]]);
		$grid->setFilter(['range' => ['from' => '', 'to' => '']]);
		Assert::true($grid->isFilterActive());

		// Default filter holds only empty values -> nothing to reset
		$grid->setDefaultFilter(['test' => '', 'range' => ['from' => '', 'to' => '']]);
		$grid->setFilter(['test' => '', 'range' => ArrayHash::from(['from' => '', 'to' => ''])]);
		Assert::false($grid->isFilterActive());

		// Reset does not go back to default -> empty filter has nothing to reset
		$grid->setDefaultFilter(['test' => 'value'], false);
		$grid->setFilter([]);
		Assert::false($grid->isFilterActive());

		// Unchanged behaviour: non-empty filter is always active
		$grid->setDefaultFilter(['test' => 'value']);
		$grid->setFilter(['test' => 'value']);
		Assert::true($grid->isFilterActive());

		$grid->setFilter(['test' => 'other']);
		Assert::true($grid->isFilterActive());
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

}

(new DatagridTest())->run();
