<?php

namespace Ublaboo\DataGrid\Tests\Cases;

use Tester\TestCase,
	Tester\Assert,
	Ublaboo;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../files/XTestingDataGridFactory.php';

final class ColumnNumberTest extends TestCase
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


	public function render($column)
	{
		$item = ['id' => 1, 'name' => 'John', 'amount' => 345678.567];

		return (string) $column->render($item);
	}


	public function testFormat()
	{
		$number = $this->grid->addColumnNumber('amount', 'Amount');
		Assert::same('345 679', $this->render($number));

		$number = $this->grid->addColumnNumber('amount2', 'Amount', 'amount')->format('2', '.', ',');
		Assert::same('345,678.57', $this->render($number));
	}

}


$test_case = new ColumnNumberTest;
$test_case->run();
