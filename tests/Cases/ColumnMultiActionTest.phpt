<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases;

use Contributte\Datagrid\Column\Action;
use Contributte\Datagrid\Column\MultiAction;
use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Exception\DatagridException;
use Contributte\Datagrid\Row;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/TestingDatagridFactory.php';

final class ColumnMultiActionTest extends TestCase
{

	private Datagrid $grid;

	public function setUp(): void
	{
		$factory = new TestingDatagridFactory();
		$this->grid = $factory->createTestingDatagrid();
	}

	public function testActionRegistration(): void
	{
		$multiAction = new MultiAction($this->grid, 'actions', 'Actions');
		$return = $multiAction->addAction('detail', 'Detail');

		Assert::same($multiAction, $return);
		Assert::count(1, $multiAction->getActions());
		Assert::type(Action::class, $multiAction->getAction('detail'));
		Assert::same(
			'dropdown-item datagrid-multiaction-dropdown-item',
			$multiAction->getAction('detail')->getClass(new Row($this->grid, ['id' => 1], 'id'))
		);
	}

	public function testDuplicateActionThrowsException(): void
	{
		$multiAction = new MultiAction($this->grid, 'actions', 'Actions');
		$multiAction->addAction('detail', 'Detail');

		Assert::exception(
			fn () => $multiAction->addAction('detail', 'Detail'),
			DatagridException::class,
			'There is already action at key [detail] defined for MultiAction.'
		);
	}

	public function testMissingActionThrowsException(): void
	{
		$multiAction = new MultiAction($this->grid, 'actions', 'Actions');

		Assert::exception(
			fn () => $multiAction->getAction('missing'),
			DatagridException::class,
			'There is no action at key [missing] defined for MultiAction.'
		);
	}

	public function testRowCondition(): void
	{
		$multiAction = new MultiAction($this->grid, 'actions', 'Actions');
		$row = new Row($this->grid, ['id' => 1, 'enabled' => true], 'id');
		$disabledRow = new Row($this->grid, ['id' => 2, 'enabled' => false], 'id');

		Assert::true($multiAction->testRowCondition('detail', $row));

		$multiAction->setRowCondition('detail', fn (array $item): bool => $item['enabled']);

		Assert::true($multiAction->testRowCondition('detail', $row));
		Assert::false($multiAction->testRowCondition('detail', $disabledRow));
	}

	public function testTemplateVariablesContainMultiAction(): void
	{
		$multiAction = new MultiAction($this->grid, 'actions', 'Actions');

		Assert::same($multiAction, $multiAction->getTemplateVariables()['multiAction']);
	}

}


(new ColumnMultiActionTest())->run();
