<?php

/**
 * TEST: ItemsPerPageTest
 * @testCase Ublaboo\DataGrid\Tests\Cases\ItemsPerPageTest
 */

namespace Ublaboo\DataGrid\Tests\Cases;

use Tester;
use Ublaboo;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/XTestingDataGridFactory.php';


class ItemsPerPageTest extends Tester\TestCase
{

	/**
	 * @var Ublaboo\DataGrid\DataGrid
	 */
	private $grid;


	public function setUp()
	{
		$factory = new Ublaboo\DataGrid\Tests\Files\XTestingDataGridFactory;
		$this->grid = $factory->createXTestingDataGrid();
	}


	public function testGetPerPage()
	{
	    $this->grid->setItemsPerPageList([10, 20, 50], false);

        $this->grid->per_page = 20;
        Tester\Assert::same(20, $this->grid->getPerPage());

        $this->grid->per_page = 'all';
        Tester\Assert::same(10, $this->grid->getPerPage());
	}

    public function testGetPerPageAll()
    {
        $this->grid->setItemsPerPageList([10, 20, 50], true);

        $this->grid->per_page = 20;
        Tester\Assert::same(20, $this->grid->getPerPage());

        $this->grid->per_page = 'all';
        Tester\Assert::same('all', $this->grid->getPerPage());
    }

	public function testGetPerPageAllTranslated()
    {
        $translator = new Ublaboo\DataGrid\Localization\SimpleTranslator([
            'ublaboo_datagrid.all' => 'všechny'
        ]);
        $this->grid->setTranslator($translator);

        $this->grid->setItemsPerPageList([10, 20, 50], true);

        $this->grid->per_page = 20;
        Tester\Assert::same(20, $this->grid->getPerPage());

        $this->grid->per_page = 'all';
        Tester\Assert::same('all', $this->grid->getPerPage());
    }
}

$test = new ItemsPerPageTest();
$test->run();
