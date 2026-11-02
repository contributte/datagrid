<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases;

use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Storage\IStateStorage;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactoryRouter;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

final class HideableColumnsTest extends TestCase
{

	private Datagrid $grid;

	private IStateStorage $storage;

	public function testIsHideable(): void
	{
		Assert::true($this->grid->getColumn('id')->isHideable());
		Assert::false($this->grid->getColumn('name')->isHideable());
	}

	public function testHideColumn(): void
	{
		$this->grid->handleHideColumn('id');

		Assert::same(['id'], $this->storage->loadState('_grid_hidden_columns'));
		Assert::same(['name', 'status'], array_keys($this->grid->getColumns()));
	}

	public function testUnhideableColumnCannotBeHidden(): void
	{
		$this->grid->handleHideColumn('name');

		Assert::null($this->storage->loadState('_grid_hidden_columns'));
		Assert::same(['id', 'name', 'status'], array_keys($this->grid->getColumns()));
	}

	public function testUnhideableColumnIgnoresStoredAndDefaultHide(): void
	{
		$this->grid->getColumn('name')->setDefaultHide();
		$this->storage->saveState('_grid_hidden_columns', ['name', 'status']);
		$this->storage->saveState('_grid_hidden_columns_manipulated', true);

		Assert::same(['id', 'name'], array_keys($this->grid->getColumns()));
	}

	public function testUnhideableColumnDefaultHide(): void
	{
		$this->grid->getColumn('name')->setDefaultHide();
		$this->grid->getColumn('status')->setDefaultHide();

		Assert::same(['id', 'name'], array_keys($this->grid->getColumns()));
		Assert::same(['status'], $this->storage->loadState('_grid_hidden_columns'));
	}

	protected function setUp(): void
	{
		$factory = new TestingDatagridFactoryRouter();
		$this->grid = $factory->createTestingDatagrid()->getComponent('grid');
		$this->storage = new class implements IStateStorage {

			/** @var array<string, mixed> */
			private array $state = [];

			public function loadState(string $key): mixed
			{
				return $this->state[$key] ?? null;
			}

			public function saveState(string $key, mixed $value): void
			{
				$this->state[$key] = $value;
			}

			public function deleteState(string $key): void
			{
				unset($this->state[$key]);
			}

		};
		$this->grid->setStateStorage($this->storage);
		$this->grid->setColumnsHideable();
		$this->grid->addColumnText('id', 'Id');
		$this->grid->addColumnText('name', 'Name')->setHideable(false);
		$this->grid->addColumnText('status', 'Status');
	}

}

(new HideableColumnsTest())->run();
