<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases;

require __DIR__ . '/../bootstrap.php';

use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Tests\Files\FormValueObject;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactoryRouter;
use Nette\Application\AbortException;
use Nette\Forms\Container;
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

	/**
	 * This case is testing grid filter processing to not cause side effects by unnecessarily instantiating
	 * value object {@see FormValueObject} of inline add form container. This value object is crafted
	 * to fail on constructor argument type check due to inline add form container not being validated in this case.
	 */
	public function testFilterSubmitWithInvalidInlineAddOpen(): void
	{
		$factory = new TestingDataGridFactoryRouter();
		/** @var Datagrid $grid */
		$grid = $factory->createTestingDataGrid()->getComponent('grid');

		$grid->addColumnText('status', 'Status');

		$grid->addInlineAdd()->onControlAdd[] = function (Container $container): void {
			$container->setMappedType(FormValueObject::class);
			$container->addSelect('status', '', [
				// items are irrelevant, case is testing control returning null value
				1 => 'Concept',
				2 => 'Active',
				3 => 'Unpublished',
			])
				->setPrompt('---')
				->setRequired();
		};

		$filterForm = $grid->createComponentFilter();

		Assert::exception(function () use ($grid, $filterForm): void {
			$grid->filterSucceeded($filterForm);
		}, AbortException::class);
	}

	public function testEmptyFiltersAreNotAddedToPersistentParameter(): void
	{
		$factory = new TestingDatagridFactoryRouter();
		/** @var Datagrid $grid */
		$grid = $factory->createTestingDatagrid()->getComponent('grid');

		// Add multiple filters
		$grid->addFilterText('name', 'Name');
		$grid->addFilterText('email', 'Email');
		$grid->addFilterText('status', 'Status');

		$filterForm = $grid->createComponentFilter();

		// Set only some filters with values
		$filterForm['filter']['name']->setValue('John');
		$filterForm['filter']['email']->setValue(''); // Empty value
		$filterForm['filter']['status']->setValue(null); // Null value

		Assert::exception(function () use ($grid, $filterForm): void {
			$grid->filterSucceeded($filterForm);
		}, AbortException::class);

		Assert::count(1, $grid->filter);
		Assert::true(isset($grid->filter['name']));
		Assert::equal('John', $grid->filter['name']);
		Assert::false(isset($grid->filter['email']));
		Assert::false(isset($grid->filter['status']));
	}

	public function testAllEmptyFiltersResultInNoFilterPersistence(): void
	{
		$factory = new TestingDatagridFactoryRouter();
		/** @var Datagrid $grid */
		$grid = $factory->createTestingDatagrid()->getComponent('grid');

		$grid->addFilterText('name', 'Name');
		$grid->addFilterText('email', 'Email');

		$filterForm = $grid->createComponentFilter();

		// Set all filters to empty values
		$filterForm['filter']['name']->setValue('');
		$filterForm['filter']['email']->setValue('');

		Assert::exception(function () use ($grid, $filterForm): void {
			$grid->filterSucceeded($filterForm);
		}, AbortException::class);

		Assert::count(0, $grid->filter);
	}

}

(new FilterTest())->run();
