<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Tests\Cases;

require __DIR__ . '/../bootstrap.php';

use Nette\Application\AbortException;
use Tester\Assert;
use Tester\TestCase;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Tests\Files\TestingDataGridFactoryRouter;

final class DataGridTest extends TestCase
{

	public function testDefaultFilter(): void
	{
		$factory = new TestingDataGridFactoryRouter();
		/** @var DataGrid $grid */
		$grid = $factory->createTestingDataGrid()->getComponent('grid');
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
		$factory = new TestingDataGridFactoryRouter();
		/** @var DataGrid $grid */
		$grid = $factory->createTestingDataGrid()->getComponent('grid');
		$grid->setRememberState(true);

		Assert::exception(function () use ($grid): void {
			$grid->handleResetFilter();
		}, AbortException::class);
	}

	public function testResetFilterLinkWithNoRememberOption(): void
	{
		$factory = new TestingDataGridFactoryRouter();
		/** @var DataGrid $grid */
		$grid = $factory->createTestingDataGrid()->getComponent('grid');
		$grid->setRememberState(false);

		Assert::exception(function () use ($grid): void {
			$grid->handleResetFilter();
		}, AbortException::class);
	}

}

(new DataGridTest())->run();
