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

	private Datagrid $datagrid;

	public function setUp(): void
	{
		$factory = new TestingDatagridFactory();
		$this->datagrid = $factory->createTestingDatagrid();
	}

	public function testSetSortable(): void
	{
		$this->datagrid->onColumnAdd[] = function ($key, Column $column): void {
			$column->setSortable();
		};

		$columnText = $this->datagrid->addColumnText('text', 'textName');
		$columnNumber = $this->datagrid->addColumnNumber('number', 'numberName');
		$columnDateTime = $this->datagrid->addColumnDateTime('dateTime', 'dateTimeName');

		$columnTextNotSortable = $this->datagrid->addColumnText('textNotSortable', 'textName')
			->setSortable(false);

		Assert::true($columnText->isSortable());
		Assert::true($columnNumber->isSortable());
		Assert::true($columnDateTime->isSortable());

		Assert::false($columnTextNotSortable->isSortable());
	}

}

$test = new OnColumnAddCallbackTest();
$test->run();
