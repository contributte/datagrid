<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\Tests\Cases;

require __DIR__ . '/../bootstrap.php';

use Nette\Application\AbortException;
use Tester\Assert;
use Tester\TestCase;
use Ublaboo\DataGrid\DataGrid;
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

}

(new FilterTest)->run();
