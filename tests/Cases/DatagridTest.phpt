<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases;

require __DIR__ . '/../bootstrap.php';

use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactoryRouter;
use Nette\Application\AbortException;
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

}

(new DatagridTest())->run();
