<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\Tests\Cases;

require __DIR__ . '/../bootstrap.php';

use Nette\Application\AbortException;
use Tester\Assert;
use Tester\TestCase;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Tests\Files\TestingDataGridFactory;
use Ublaboo\DataGrid\Tests\Files\TestingDataGridFactoryRouter;

final class FilterTest extends TestCase
{

	public function testFilterSubmit(): void
	{
		$factory = new TestingDataGridFactoryRouter();
		/** @var DataGrid $grid */
		$grid = $factory->createTestingDataGrid()->getComponent('grid');
		$filterForm = $grid->createComponentFilter();

		Assert::exception(function() use ($grid, $filterForm): void {
			$grid->filterSucceeded($filterForm);
		}, AbortException::class);
	}

	public function testFilterRendering(): void
	{
		$factory = new TestingDataGridFactory();
		/** @var DataGrid $grid */
		$grid = $factory->createTestingDataGrid();

		$grid->addFilterText('default', 'default');
		$grid->assembleFilters();
		Assert::false($grid->hasOuterFilterRendering());
		Assert::true($grid->hasColumnFilterRendering());

		$grid->setOuterFilterRendering();
		$grid->assembleFilters();
		Assert::true($grid->hasOuterFilterRendering());
		Assert::false($grid->hasColumnFilterRendering());

		$grid->removeFilter('default');
		$filters = $grid->assembleFilters();
		Assert::count(0, $filters);

		$grid->addFilterText('outerFilter', 'outerFilter')
			->setOuterRendering();
		$grid->assembleFilters();
		Assert::true($grid->hasOuterFilterRendering());
		Assert::false($grid->hasColumnFilterRendering());

		$grid->addFilterText('columnFilter', 'columnFilter');
		Assert::true($grid->hasOuterFilterRendering());
		Assert::true($grid->hasColumnFilterRendering());
	}

}

(new FilterTest)->run();
