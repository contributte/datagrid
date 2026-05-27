<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases;

use Contributte\Datagrid\Column\Action\Confirmation\CallbackConfirmation;
use Contributte\Datagrid\Column\Action\Confirmation\StringConfirmation;
use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Row;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/TestingDatagridFactory.php';

final class ColumnStatusTest extends TestCase
{

	private Datagrid $grid;

	public function setUp(): void
	{
		$factory = new TestingDatagridFactory();
		$this->grid = $factory->createTestingDatagrid();
	}

	public function testStatus(): void
	{
		$grid = $this->grid;

		$grid->addColumnStatus('status', 'Status')
			->addOption(1, 'Online')
			->setIcon('check')
			->setClass('btn-success')
			->endOption()
			->addOption(2, 'Standby')
			->setIcon('user')
			->setClass('btn-primary')
			->endOption()
			->addOption(3, 'Offline')
			->setIcon('close')
			->setClass('btn-danger')
			->endOption()
			->onChange[] = [$this, 'statusChange'];

		$status = $grid->getColumn('status');

		Assert::same('status', $status->getKey());
		Assert::same(3, count($status->getOptions()));

		$row = new Row($grid, ['id' => 10, 'status' => 2], 'id');

		$current_option = $status->getCurrentOption($row);

		Assert::same(2, $current_option->getValue());
	}

	public function testRemoveColumn(): void
	{
		$grid = $this->grid;
		$grid->addColumnText('test', 'Test');
		$grid->removeColumn('test');
		$grid->getColumnsVisibility();
	}

	public function testOptionSettersAndGetters(): void
	{
		$option = $this->grid->addColumnStatus('status', 'Status')
			->addOption(1, 'Online');

		Assert::same($option, $option->setTitle('Currently online'));
		Assert::same($option, $option->setClass('btn-primary', 'btn btn-sm'));
		Assert::same($option, $option->setClassSecondary('btn btn-secondary'));
		Assert::same($option, $option->setClassInDropdown('dropdown-item active'));
		Assert::same($option, $option->setIcon('check'));
		Assert::same($option, $option->setIconSecondary('circle'));

		Assert::same(1, $option->getValue());
		Assert::same('Online', $option->getText());
		Assert::same('Currently online', $option->getTitle());
		Assert::same('btn-primary', $option->getClass());
		Assert::same('btn btn-secondary', $option->getClassSecondary());
		Assert::same('dropdown-item active', $option->getClassInDropdown());
		Assert::same('check', $option->getIcon());
		Assert::same('circle', $option->getIconSecondary());
	}

	public function testOptionConfirmationDialog(): void
	{
		$row = new Row($this->grid, ['id' => 10, 'name' => 'John'], 'id');
		$columnStatus = $this->grid->addColumnStatus('status', 'Status');

		$withoutConfirmation = $columnStatus->addOption(1, 'Online');
		Assert::null($withoutConfirmation->getConfirmationDialog($row));

		$stringConfirmation = $columnStatus->addOption(2, 'Offline')
			->setConfirmation(new StringConfirmation('Really?'));
		Assert::same('Really?', $stringConfirmation->getConfirmationDialog($row));

		$placeholderConfirmation = $columnStatus->addOption(3, 'Blocked')
			->setConfirmation(new StringConfirmation('Block %s?', 'name'));
		Assert::same('Block John?', $placeholderConfirmation->getConfirmationDialog($row));

		$callbackConfirmation = $columnStatus->addOption(4, 'Deleted')
			->setConfirmation(new CallbackConfirmation(fn (array $item): string => 'Delete #' . $item['id'] . '?'));
		Assert::same('Delete #10?', $callbackConfirmation->getConfirmationDialog($row));
	}

}


(new ColumnStatusTest())->run();
