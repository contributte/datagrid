<?php

declare(strict_types=1);

/**
 * TEST: OnColumnAddCallbackTest
 *
 * @testCase Ublaboo\DataGrid\Tests\Cases\OnColumnAddCallbackTest
 */

namespace Ublaboo\DataGrid\Tests\Cases;

use Tester;
use Ublaboo;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/TestingDataGridFactory.php';


class OnColumnAddCallbackTest extends Tester\TestCase
{

	/**
	 * @var Ublaboo\DataGrid\DataGrid
	 */
	private $grid;

	public function setUp(): void
	{
		$factory = new Ublaboo\DataGrid\Tests\Files\TestingDataGridFactory();
		$this->grid = $factory->createTestingDataGrid();
	}


	public function testSetSortable(): void
	{
		$this->grid->onColumnAdd[] = function ($key, Ublaboo\DataGrid\Column\Column $column): void {
			$column->setSortable();
		};

		$columnText = $this->grid->addColumnText('text', 'textName');
		$columnNumber = $this->grid->addColumnNumber('number', 'numberName');
		$columnDateTime = $this->grid->addColumnDateTime('dateTime', 'dateTimeName');

		$columnTextNotSortable = $this->grid->addColumnText('textNotSortable', 'textName')
			->setSortable(false);

		Tester\Assert::true($columnText->isSortable());
		Tester\Assert::true($columnNumber->isSortable());
		Tester\Assert::true($columnDateTime->isSortable());

		Tester\Assert::false($columnTextNotSortable->isSortable());
	}

}

$test = new OnColumnAddCallbackTest();
$test->run();
