<?php declare(strict_types = 1);

/**
 * TEST: ItemsPerPageTest
 *
 * @testCase Contributte\Datagrid\Tests\Cases\ItemsPerPageTest
 */

namespace Contributte\Datagrid\Tests\Cases;

use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Localization\SimpleTranslator;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/TestingDatagridFactory.php';


class ItemsPerPageTest extends TestCase
{

	private Datagrid $grid;

	public function setUp(): void
	{
		$factory = new TestingDatagridFactory();
		$this->grid = $factory->createTestingDatagrid();
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
			'contributte_datagrid.all' => 'všechny',
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
