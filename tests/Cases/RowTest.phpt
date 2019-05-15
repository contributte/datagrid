<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\Tests\Cases;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/TestingDataGridFactory.php';

use Nette\Utils\Html;
use Tester\Assert;
use Tester\TestCase;
use Ublaboo;
use Ublaboo\DataGrid\Row;
use Ublaboo\DataGrid\Tests\Cases\Utils\TestingDDataGridEntity;
use Ublaboo\DataGrid\Tests\Cases\Utils\TestingLMDataGridEntity;
use Ublaboo\DataGrid\Tests\Cases\Utils\TestingLMDataGridEntity2;
use Ublaboo\DataGrid\Tests\Files\TestingDataGridFactory;

final class RowTest extends TestCase
{

	/**
	 * @var Ublaboo\DataGrid\DataGrid
	 */
	private $grid;

	public function setUp(): void
	{
		$factory = new TestingDataGridFactory();
		$this->grid = $factory->createTestingDataGrid();
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


	public function testLeanMapperEntity(): void
	{
		$entity = new TestingLMDataGridEntity(['id' => 20, 'name' => 'John Doe', 'age' => 23]);
		$entity2 = new TestingLMDataGridEntity2(['id' => 21, 'name' => 'Francis', 'age' => 20]);

		$entity->setGirlfriend($entity2);

		$row = new Row($this->grid, $entity, 'id');

		Assert::same('John Doe', $row->getValue('name'));
		Assert::same(23, $row->getValue('age'));
		Assert::same(20, $row->getValue('girlfriend.age'));
	}


	public function testDoctrineEntity(): void
	{
		$entity = new TestingDDataGridEntity(['id' => 20, 'name' => 'John Doe', 'age' => 23]);
		$entity2 = new TestingDDataGridEntity(['id' => 21, 'name' => 'Francis', 'age' => 20]);

		$entity->setPartner($entity2);

		$row = new Row($this->grid, $entity, 'id');

		Assert::same(20, $row->getId());
		Assert::same('John Doe', $row->getValue('name'));
		Assert::same(23, $row->getValue('age'));
		Assert::same(20, $row->getValue('partner.age'));
	}

}


(new RowTest())->run();
