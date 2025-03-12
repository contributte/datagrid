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
		/** @var \Ublaboo\DataGrid\Datagrid $grid */
		$grid = $factory->createTestingDataGrid()->getComponent('grid');

		$grid->addColumnText('status', 'Status');

		$grid->addInlineAdd()->onControlAdd[] = function (Container $container) {
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

		Assert::exception(function() use ($grid, $filterForm): void {
			$grid->filterSucceeded($filterForm);
		}, AbortException::class);
	}

}

(new FilterTest())->run();
