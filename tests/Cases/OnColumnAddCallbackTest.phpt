<?php declare(strict_types = 1);

/**
 * TEST: OnColumnAddCallbackTest
 *
 * @testCase Contributte\Datagrid\Tests\Cases\OnColumnAddCallbackTest
 */

namespace Contributte\Datagrid\Tests\Cases;

use Contributte\Datagrid\Column\Column;
use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/TestingDatagridFactory.php';


class OnColumnAddCallbackTest extends TestCase
{

	private Datagrid $grid;

	public function setUp(): void
	{
		$factory = new TestingDatagridFactory();
		$this->grid = $factory->createTestingDatagrid();
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
