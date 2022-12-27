<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Tests\Cases;

use Tester\Assert;
use Tester\TestCase;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Row;
use Ublaboo\DataGrid\Tests\Files\TestingDataGridFactory;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/TestingDataGridFactory.php';

final class ColumnStatusTest extends TestCase
{

	private DataGrid $grid;

	public function setUp(): void
	{
		$factory = new TestingDataGridFactory();
		$this->grid = $factory->createTestingDataGrid();
	}

	public function testStatus(): void
	{
		$grid = $this->grid;

		$grid->addColumnStatus('status', 'Status')
			->setCaret(false)
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

}


(new ColumnStatusTest())->run();
