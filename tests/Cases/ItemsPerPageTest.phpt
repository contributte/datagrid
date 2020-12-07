<?php

declare(strict_types=1);

/**
 * TEST: ItemsPerPageTest
 * @testCase Ublaboo\DataGrid\Tests\Cases\ItemsPerPageTest
 */

namespace Ublaboo\DataGrid\Tests\Cases;

use Tester;
use Ublaboo;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/TestingDataGridFactory.php';


class ItemsPerPageTest extends Tester\TestCase
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


	public function testGetPerPage(): void
	{
		$this->grid->setItemsPerPageList([10, 20, 50], false);

		$this->grid->perPage = 20;
		Tester\Assert::same(20, $this->grid->getPerPage());

		$this->grid->perPage = 'all';
		Tester\Assert::same(10, $this->grid->getPerPage());
	}

	public function testGetPerPageAll(): void
	{
		$this->grid->setItemsPerPageList([10, 20, 50], true);

		$this->grid->perPage = 20;
		Tester\Assert::same(20, $this->grid->getPerPage());

		$this->grid->perPage = 'all';
		Tester\Assert::same('all', $this->grid->getPerPage());
	}

	public function testGetPerPageAllTranslated(): void
	{
		$translator = new Ublaboo\DataGrid\Localization\SimpleTranslator([
			'ublaboo_datagrid.all' => 'všechny',
		]);
		$this->grid->setTranslator($translator);

		$this->grid->setItemsPerPageList([10, 20, 50], true);

		$this->grid->perPage = 20;
		Tester\Assert::same(20, $this->grid->getPerPage());

		$this->grid->perPage = 'all';
		Tester\Assert::same('all', $this->grid->getPerPage());
	}
}

$test = new ItemsPerPageTest();
$test->run();
