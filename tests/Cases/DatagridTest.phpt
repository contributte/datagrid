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
		/** @var Datagrid $datagrid */
		$datagrid = $factory->createTestingDatagrid()->getComponent('datagrid');
		$datagrid->addFilterText('test', 'Test filter');
		$datagrid->setDefaultFilter([
			'test' => 'value',
		]);

		$datagrid->setFilter(['test' => 'value']);
		Assert::true($datagrid->isFilterDefault());

		$datagrid->setFilter(['test' => null]);
		Assert::false($datagrid->isFilterDefault());
	}

	public function testResetFilterLinkWithRememberOption(): void
	{
		$factory = new TestingDatagridFactoryRouter();
		/** @var Datagrid $datagrid */
		$datagrid = $factory->createTestingDatagrid()->getComponent('datagrid');
		$datagrid->setRememberState(true);

		Assert::exception(function () use ($datagrid): void {
			$datagrid->handleResetFilter();
		}, AbortException::class);
	}

	public function testResetFilterLinkWithNoRememberOption(): void
	{
		$factory = new TestingDatagridFactoryRouter();
		/** @var Datagrid $datagrid */
		$datagrid = $factory->createTestingDatagrid()->getComponent('datagrid');
		$datagrid->setRememberState(false);

		Assert::exception(function () use ($datagrid): void {
			$datagrid->handleResetFilter();
		}, AbortException::class);
	}

}

(new DatagridTest())->run();
