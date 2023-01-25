<?php declare(strict_types = 1);

/**
 * TEST: ItemsPerPageTest
 *
 * @testCase Ublaboo\DataGrid\Tests\Cases\ItemsPerPageTest
 */

namespace Ublaboo\DataGrid\Tests\Cases;

use Tester\Assert;
use Tester\TestCase;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Localization\SimpleTranslator;
use Ublaboo\DataGrid\Tests\Files\TestingDataGridFactory;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/TestingDataGridFactory.php';


class ItemsPerPageTest extends TestCase
{

	private DataGrid $grid;

	public function setUp(): void
	{
		$factory = new TestingDataGridFactory();
		$this->grid = $factory->createTestingDataGrid();
	}

	public function testGetPerPage(): void
	{
		$this->grid->setItemsPerPageList([10, 20, 50], false);

		$this->grid->perPage = 20;
		Assert::same(20, $this->grid->getPerPage());

		$this->grid->perPage = 'all';
		Assert::same(10, $this->grid->getPerPage());
	}

	public function testGetPerPageAll(): void
	{
		$this->grid->setItemsPerPageList([10, 20, 50], true);

		$this->grid->perPage = 20;
		Assert::same(20, $this->grid->getPerPage());

		$this->grid->perPage = 'all';
		Assert::same('all', $this->grid->getPerPage());
	}

	public function testGetPerPageAllTranslated(): void
	{
		$translator = new SimpleTranslator([
			'ublaboo_datagrid.all' => 'všechny',
		]);
		$this->grid->setTranslator($translator);

		$this->grid->setItemsPerPageList([10, 20, 50], true);

		$this->grid->perPage = 20;
		Assert::same(20, $this->grid->getPerPage());

		$this->grid->perPage = 'all';
		Assert::same('all', $this->grid->getPerPage());
	}

}

$test = new ItemsPerPageTest();
$test->run();
