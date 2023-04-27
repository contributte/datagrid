<?php declare(strict_types = 1);

/**
 * TEST: OnColumnAddCallbackTest
 *
 * @testCase Ublaboo\DataGrid\Tests\Cases\OnColumnAddCallbackTest
 */

namespace Ublaboo\DataGrid\Tests\Cases;

use Tester\Assert;
use Tester\TestCase;
use Ublaboo\DataGrid\Column\Column;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Tests\Files\TestingDataGridFactory;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/TestingDataGridFactory.php';


class OnColumnAddCallbackTest extends TestCase
{

	private DataGrid $grid;

	public function setUp(): void
	{
		$factory = new TestingDataGridFactory();
		$this->grid = $factory->createTestingDataGrid();
	}

	public function testSetSortable(): void
	{
		$this->grid->onColumnAdd[] = function ($key, Column $column): void {
			$column->setSortable();
		};

		$columnText = $this->grid->addColumnText('text', 'textName');
		$columnNumber = $this->grid->addColumnNumber('number', 'numberName');
		$columnDateTime = $this->grid->addColumnDateTime('dateTime', 'dateTimeName');

		$columnTextNotSortable = $this->grid->addColumnText('textNotSortable', 'textName')
			->setSortable(false);

		Assert::true($columnText->isSortable());
		Assert::true($columnNumber->isSortable());
		Assert::true($columnDateTime->isSortable());

		Assert::false($columnTextNotSortable->isSortable());
	}

}

$test = new OnColumnAddCallbackTest();
$test->run();
