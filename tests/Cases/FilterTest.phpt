<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases;

require __DIR__ . '/../bootstrap.php';

use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Tests\Files\FormValueObject;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactoryRouter;
use Nette\Application\AbortException;
use Nette\Forms\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Utils\ArrayHash;
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

	/**
	 * Regression test for https://github.com/contributte/datagrid/issues/1281
	 *
	 * A FilterSelect on a hidden column isn't rendered in HTML, so no value is
	 * submitted for it. SelectBox's constructor adds an implicit "Filled" rule
	 * when no prompt is set, which used to fail validation for the missing
	 * value, invalidate the filter container, and trigger an E_USER_WARNING
	 * from Container::getValues() in Datagrid::filterSucceeded().
	 */
	public function testFilterSelectHasNoImplicitValidation(): void
	{
		$factory = new TestingDatagridFactoryRouter();
		/** @var Datagrid $grid */
		$grid = $factory->createTestingDatagrid()->getComponent('grid');

		$grid->addColumnText('name', 'Name')
			->setFilterText();

		$grid->addColumnText('status', 'Status')
			->setFilterSelect(['yes' => 'Yes', 'no' => 'No']);

		$filterForm = $grid->createComponentFilter();

		$filterContainer = $filterForm->getComponent('filter');
		Assert::type(Container::class, $filterContainer);

		$filterContainer->validate();

		// With no value set (as if the select was hidden and not submitted),
		// the container must still be valid — no implicit Filled rule should
		// fire on the FilterSelect.
		Assert::true($filterContainer->isValid());
	}

	/**
	 * Regression test for https://github.com/contributte/datagrid/issues/621
	 *
	 * Inputs of hidden columns are not rendered, so their empty values must not
	 * be passed to InlineEdit::onSubmit (saving them would wipe the data).
	 */
	public function testInlineEditSubmitSkipsHiddenColumns(): void
	{
		Assert::same([[], ['name', 'status']], $this->submitInlineEdit([]));
		Assert::same([['name'], ['status']], $this->submitInlineEdit(['name']));
	}

	/**
	 * @param list<string> $hiddenColumns
	 * @return mixed[]
	 */
	private function submitInlineEdit(array $hiddenColumns): array
	{
		$factory = new TestingDatagridFactoryRouter();
		/** @var Datagrid $grid */
		$grid = $factory->createTestingDatagrid()->getComponent('grid');

		$grid->setColumnsHideable();
		$grid->addColumnText('name', 'Name');
		$grid->addColumnText('status', 'Status');
		$grid->saveStorageData('_grid_hidden_columns', $hiddenColumns);
		$grid->saveStorageData('_grid_hidden_columns_manipulated', true);

		$result = new ArrayHash();
		$inlineEdit = $grid->addInlineEdit();
		$inlineEdit->onControlAdd[] = function (Container $container): void {
			$container->addText('name');
			$container->addText('status');
		};
		$inlineEdit->onSubmit[] = function ($id, ArrayHash $values, array $hidden = ['missing']) use ($result): void {
			$result->hidden = $hidden;
			$result->keys = array_keys((array) $values);
		};
		$inlineEdit->onCustomRedraw[] = function (): void {
		};

		$filterForm = $grid->createComponentFilter();
		$submit = $filterForm['inline_edit']['submit'];
		Assert::type(SubmitButton::class, $submit);
		$filterForm->setSubmittedBy($submit);

		$grid->filterSucceeded($filterForm);

		return [$result->hidden ?? null, $result->keys ?? null];
	}

}

(new FilterTest())->run();
