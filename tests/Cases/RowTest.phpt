<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/TestingDatagridFactory.php';

use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Row;
use Contributte\Datagrid\Tests\Cases\Utils\LeanBookEntity;
use Contributte\Datagrid\Tests\Cases\Utils\TestingDDatagridEntity;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;
use Nette\Utils\Html;
use Tester\Assert;
use Tester\TestCase;

final class RowTest extends TestCase
{

	private Datagrid $grid;

	public function setUp(): void
	{
		$factory = new TestingDatagridFactory();
		$this->grid = $factory->createTestingDatagrid();
	}

	public function testControl(): void
	{
		$item = ['id' => 20, 'name' => 'John Doe'];
		$callback = function ($item, Html $row): void {
			$row->addClass('bg-warning');
		};

		$row = new Row($this->grid, $item, 'id');
		$callback($item, $row->getControl());

		Assert::same(20, $row->getId());
		Assert::same('bg-warning', $row->getControlClass());
	}

	public function testArray(): void
	{
		$item = ['id' => 20, 'name' => 'John Doe'];

		$row = new Row($this->grid, $item, 'id');

		Assert::same(20, $row->getId());
		Assert::same('John Doe', $row->getValue('name'));
	}

	public function testObject(): void
	{
		$item = (object) ['id' => 20, 'name' => 'John Doe'];

		$row = new Row($this->grid, $item, 'id');

		Assert::same(20, $row->getId());
	}

	public function testDoctrineEntity(): void
	{
		$entity = new TestingDDatagridEntity(['id' => 20, 'name' => 'John Doe', 'age' => 23]);
		$entity2 = new TestingDDatagridEntity(['id' => 21, 'name' => 'Francis', 'age' => 20]);

		$entity->setPartner($entity2);

		$row = new Row($this->grid, $entity, 'id');

		Assert::same(20, $row->getId());
		Assert::same('John Doe', $row->getValue('name'));
		Assert::same(23, $row->getValue('age'));
		Assert::same(20, $row->getValue('partner.age'));
	}

	public function testLeanMapperEntity(): void
	{
		$entity = new LeanBookEntity();
		$entity->id = '978-80-257-1309-9';
		$entity->pageCount = 42;

		$row = new Row($this->grid, $entity, 'id');

		Assert::same('978-80-257-1309-9', $row->getValue('id'));
		Assert::same(42, $row->getValue('pageCount'));
	}

}


(new RowTest())->run();
