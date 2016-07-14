<?php

namespace Ublaboo\DataGrid\Tests\Cases;

use Tester\TestCase;
use Tester\Assert;
use Mockery;
use Ublaboo;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Filter\FilterDate;
use Ublaboo\DataGrid\Filter\FilterDateRange;
use Ublaboo\DataGrid\Filter\FilterRange;
use Ublaboo\DataGrid\Filter\FilterText;
use Ublaboo\DataGrid\Filter\FilterSelect;
use Ublaboo\DataGrid\Utils\Sorting;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/XTestingDataGridFactory.php';

final class ArrayDataSourceTest extends TestCase
{

	private $ds;
	private $data = [
		0 => ['id' => 1, 'name' => 'John Doe', 'age' => 30, 'address' => 'Blue Village 1'],
		1 => ['id' => 2, 'name' => 'Frank Frank', 'age' => 60, 'address' => 'Yellow Garded 126'],
		2 => ['id' => 3, 'name' => 'Santa Claus', 'age' => 12, 'address' => 'New York'],
		3 => ['id' => 8, 'name' => 'Jude Law', 'age' => 8, 'address' => 'Lubababa 5'],
		4 => ['id' => 30, 'name' => 'Jackie Blue', 'age' => 80, 'address' => 'Prague 678'],
		5 => ['id' => 40, 'name' => 'John Red', 'age' => 40, 'address' => 'Porto 53'],
	];

	/**
	 * @var Ublaboo\DataGrid\DataGrid
	 */
	private $grid;


	public function setUp()
	{
		$this->ds = new Ublaboo\DataGrid\DataSource\ArrayDataSource($this->data);
		$factory = new Ublaboo\DataGrid\Tests\Files\XTestingDataGridFactory;
		$this->grid = $factory->createXTestingDataGrid();
	}


	public function testGetCount()
	{
		Assert::same(6, $this->ds->getCount());
	}


	public function testGetData()
	{
		Assert::same($this->data, $this->ds->getData());
	}


	public function testFilter()
	{
		/**
		 * Single column - default filter
		 */
		$filter = new FilterText($this->grid, 'a', 'b', ['name']);
		$filter->setValue('John Red');

		$this->ds->filter([$filter]);
		Assert::same([$this->data[0], $this->data[5]], array_values($this->ds->getData()));

		/**
		 * Single column - SplitWordsSearch => FALSE
		 */
		$filter = new FilterText($this->grid, 'a', 'b', ['name']);
		$filter->setSplitWordsSearch(FALSE);
		$filter->setValue('John Red');

		$this->ds->filter([$filter]);
		Assert::same([$this->data[5]], array_values($this->ds->getData()));

		/**
		 * Multiple columns
		 */
		$this->setUp();
		$filter = new FilterText($this->grid, 'a', 'b', ['name', 'address']);
		$filter->setValue('lu');

		$this->ds->filter([$filter]);
		Assert::same([
			$this->data[0],
			$this->data[3],
			$this->data[4]
		], array_values($this->ds->getData()));
	}


	public function testFitlerOne()
	{
		$this->setUp();

		$this->ds->filterOne(['id' => 8]);

		Assert::same([$this->data[3]], array_values($this->ds->getData()));
	}


	public function testLimit()
	{
		$this->setUp();

		$this->ds->limit(2, 2);

		Assert::same([$this->data[2], $this->data[3]], array_values($this->ds->getData()));
	}


	public function testSort()
	{
		$this->setUp();

		$this->ds->sort(new Sorting(['name' => 'DESC']));

		Assert::same([
			$this->data[2],
			$this->data[3],
			$this->data[5],
			$this->data[0],
			$this->data[4],
			$this->data[1]
		], array_values($this->ds->getData()));
	}

}


$test_case = new ArrayDataSourceTest;
$test_case->run();
