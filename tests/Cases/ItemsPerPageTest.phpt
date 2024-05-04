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

	private Datagrid $datagrid;

	public function setUp(): void
	{
		$factory = new TestingDatagridFactory();
		$this->datagrid = $factory->createTestingDatagrid();
	}

	public function testGetPerPage(): void
	{
		$this->datagrid->setItemsPerPageList([10, 20, 50], false);

		$this->datagrid->perPage = 20;
		Assert::same(20, $this->datagrid->getPerPage());

		$this->datagrid->perPage = 'all';
		Assert::same(10, $this->datagrid->getPerPage());
	}

	public function testGetPerPageAll(): void
	{
		$this->datagrid->setItemsPerPageList([10, 20, 50], true);

		$this->datagrid->perPage = 20;
		Assert::same(20, $this->datagrid->getPerPage());

		$this->datagrid->perPage = 'all';
		Assert::same('all', $this->datagrid->getPerPage());
	}

	public function testGetPerPageAllTranslated(): void
	{
		$translator = new SimpleTranslator([
			'contributte_datagrid.all' => 'všechny',
		]);
		$this->datagrid->setTranslator($translator);

		$this->datagrid->setItemsPerPageList([10, 20, 50], true);

		$this->datagrid->perPage = 20;
		Assert::same(20, $this->datagrid->getPerPage());

		$this->datagrid->perPage = 'all';
		Assert::same('all', $this->datagrid->getPerPage());
	}

}

$test = new ItemsPerPageTest();
$test->run();
