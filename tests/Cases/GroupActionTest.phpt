<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases;

use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Exception\DatagridGroupActionException;
use Contributte\Datagrid\GroupAction\GroupButtonAction;
use Contributte\Datagrid\GroupAction\GroupMultiSelectAction;
use Contributte\Datagrid\GroupAction\GroupSelectAction;
use Contributte\Datagrid\GroupAction\GroupTextAction;
use Contributte\Datagrid\GroupAction\GroupTextareaAction;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/TestingDatagridFactory.php';

final class GroupActionTest extends TestCase
{

	private Datagrid $grid;

	public function setUp(): void
	{
		$factory = new TestingDatagridFactory();
		$this->grid = $factory->createTestingDatagrid();
	}

	public function testButtonActionClassDefaults(): void
	{
		Assert::same('btn btn-sm btn-success', (new GroupButtonAction('Delete'))->getClass());
		Assert::same('btn btn-danger', (new GroupButtonAction('Delete', 'btn btn-danger'))->getClass());
		Assert::same('btn btn-sm btn-success', (new GroupButtonAction('Delete', ''))->getClass());
	}

	public function testSelectActionOptions(): void
	{
		$empty = new GroupSelectAction('Empty');
		$select = new GroupSelectAction('Status', ['active' => 'Active']);

		Assert::false($empty->hasOptions());
		Assert::true($select->hasOptions());
		Assert::same(['active' => 'Active'], $select->getOptions());
	}

	public function testMultiSelectDefaultClass(): void
	{
		Assert::same('form-select form-select-sm selectpicker', (new GroupMultiSelectAction('Status'))->getClass());
	}

	public function testAttributesAndClassAreFluent(): void
	{
		$action = new GroupSelectAction('Status');

		Assert::same($action, $action->setClass('form-select'));
		Assert::same($action, $action->setAttribute('data-test', 'status'));
		Assert::same('form-select', $action->getClass());
		Assert::same(['data-test' => 'status'], $action->getAttributes());
	}

	public function testCollectionCreatesAndFindsActions(): void
	{
		$collection = $this->grid->getGroupActionCollection();

		Assert::type(GroupButtonAction::class, $collection->addGroupButtonAction('Delete'));
		Assert::type(GroupSelectAction::class, $collection->addGroupSelectAction('Status', ['active' => 'Active']));
		Assert::type(GroupMultiSelectAction::class, $collection->addGroupMultiSelectAction('Tags', ['a' => 'A']));
		Assert::type(GroupTextAction::class, $collection->addGroupTextAction('Message'));
		Assert::type(GroupTextareaAction::class, $collection->addGroupTextareaAction('Note'));

		Assert::same('Tags', $collection->getGroupAction('Tags')->getTitle());
	}

	public function testCollectionThrowsOnMissingAction(): void
	{
		$collection = $this->grid->getGroupActionCollection();

		Assert::exception(
			fn () => $collection->getGroupAction('Missing'),
			DatagridGroupActionException::class,
			'Group action Missing does not exist.'
		);
	}

}


(new GroupActionTest())->run();
