<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases;

require __DIR__ . '/../bootstrap.php';

use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Storage\NoopStateStorage;
use Contributte\Datagrid\Storage\SessionStateStorage;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactoryRouter;
use Nette\Application\AbortException;
use Tester\Assert;
use Tester\TestCase;

final class RememberStateTest extends TestCase
{

	/**
	 * This case is testing grid->setRememberState(true), in this case SessionStateStorage is used,
	 * and state is stored in session.
	 */
	public function testDefaultSessionStateStorage(): void
	{
		$grid = $this->createGridWithRememberState(true);

		Assert::type(SessionStateStorage::class, $grid->getStateStorage());

		$this->simulateFilterSubmission($grid);
		$this->assertFilterValueStored($grid, 'value');
	}

	/**
	 * This case is testing grid->setRememberState(false). NoopStateStorage is used in this case, which means
	 * that no state is stored.
	 */
	public function testNoopStateStorage(): void
	{
		$grid = $this->createGridWithRememberState(false);

		Assert::type(NoopStateStorage::class, $grid->getStateStorage());

		$this->simulateFilterSubmission($grid);
		$this->assertFilterValueStored($grid, null);
	}

	/**
	 * This case is testing grid->setColumnsHideable() side effect, in this case storage is used despite
	 * remember state being set to false. This is because hideable columns are stored in session
	 */
	public function testRememberStateWithHideableColumns(): void
	{
		$grid = $this->createGridWithRememberState(false);
		$grid->setColumnsHideable();

		Assert::type(SessionStateStorage::class, $grid->getStateStorage());

		$this->simulateFilterSubmission($grid);
		$this->assertFilterValueStored($grid, 'value');
	}

	private function createGridWithRememberState(bool $rememberState): Datagrid
	{
		$factory = new TestingDatagridFactoryRouter();
		/** @var Datagrid $grid */
		$grid = $factory->createTestingDatagrid()->getComponent('grid');
		$grid->setRememberState($rememberState);

		return $grid;
	}

	private function simulateFilterSubmission(Datagrid $grid): void
	{
		$grid->addFilterText('test', 'Test filter');
		$grid->setFilter(['test' => 'value']);

		$filterForm = $grid->createComponentFilter();
		Assert::exception(function () use ($grid, $filterForm): void {
			$grid->filterSucceeded($filterForm);
		}, AbortException::class);
	}

	private function assertFilterValueStored(Datagrid $grid, ?string $expectedValue): void
	{
		$stateStorage = $grid->getStateStorage();
		$filters = $stateStorage->loadState('_grid_filters');

		if ($expectedValue === null) {
			Assert::null($filters['test'] ?? null);
		} else {
			Assert::same($expectedValue, $filters['test'] ?? null);
		}
	}

}

(new RememberStateTest())->run();
