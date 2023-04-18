<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases;

require __DIR__ . '/../bootstrap.php';

use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactoryRouter;
use Nette\Application\AbortException;
use Tester\Assert;
use Tester\TestCase;

final class FilterTest extends TestCase
{

	public function testFilterSubmit(): void
	{
		$factory = new TestingDatagridFactoryRouter();
		/** @var Datagrid $grid */
		$grid = $factory->createTestingDatagrid()->getComponent('grid');
		$filterForm = $grid->createComponentFilter();

		Assert::exception(function () use ($grid, $filterForm): void {
			$grid->filterSucceeded($filterForm);
		}, AbortException::class);
	}

}

(new FilterTest())->run();
