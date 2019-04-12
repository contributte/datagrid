<?php

namespace Ublaboo\DataGrid\Tests\Cases;

use Nette\Utils\Html;
use Tester\Environment;
use Tester\TestCase;
use Tester\Assert;
use Ublaboo;
use Ublaboo\DataGrid\Tests\Files\XTestingDDataGridEntity;
use Ublaboo\DataGrid\Tests\Files\XTestingLMDataGridEntity;
use Ublaboo\DataGrid\Tests\Files\XTestingLMDataGridEntity2;

require __DIR__ . '/../bootstrap.php';

final class RowTest extends TestCase
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


	public function testControl()
	{
		$item = ['id' => 20, 'name' => 'John Doe'];
		$callback = function ($item, Html $row) {
			$row->addClass('bg-warning');
		};

		$row = new Ublaboo\DataGrid\Row($this->grid, $item, 'id');
		$callback($item, $row->getControl());

		Assert::same(20, $row->getId());
		Assert::same('bg-warning', $row->getControlClass());
	}


	public function testArray()
	{
		$item = ['id' => 20, 'name' => 'John Doe'];

		$row = new Ublaboo\DataGrid\Row($this->grid, $item, 'id');

		Assert::same(20, $row->getId());
		Assert::same('John Doe', $row->getValue('name'));
	}


	public function testObject()
	{
		$item = (object) ['id' => 20, 'name' => 'John Doe'];

		$row = new Ublaboo\DataGrid\Row($this->grid, $item, 'id');

		Assert::same(20, $row->getId());
	}


	public function testDoctrineEntity()
	{
		$entity = new XTestingDDataGridEntity(['id' => 20, 'name' => 'John Doe', 'age' => 23]);
		$entity2 = new XTestingDDataGridEntity(['id' => 21, 'name' => 'Francis', 'age' => 20]);

		$entity->setPartner($entity2);

		$row = new Ublaboo\DataGrid\Row($this->grid, $entity, 'id');

		Assert::same(20, $row->getId());
		Assert::same('John Doe', $row->getValue('name'));
		Assert::same(23, $row->getValue('age'));
		Assert::same(20, $row->getValue('partner.age'));
	}


	public function testLeanMapperEntity()
	{
		if (!class_exists('LeanMapper\Entity')) {
			Environment::skip('Test requires LeanMapper dependency.');
		}

		$entity = new XTestingLMDataGridEntity(['id' => 20, 'name' => 'John Doe', 'age' => 23]);
		$entity2 = new XTestingLMDataGridEntity2(['id' => 21, 'name' => 'Francis', 'age' => 20]);

		$entity->setGirlfriend($entity2);

		$row = new Ublaboo\DataGrid\Row($this->grid, $entity, 'id');

		Assert::same('John Doe', $row->getValue('name'));
		Assert::same(23, $row->getValue('age'));
		Assert::same(20, $row->getValue('girlfriend.age'));
	}

}


$test_case = new RowTest;
$test_case->run();
