<?php

namespace Ublaboo\DataGrid\Tests\Cases;

use Tester\TestCase;
use Tester\Assert;
use Ublaboo;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/XTestingDataGridFactory.php';

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
		$item = new Ublaboo\DataGrid\Row($this->grid, ['id' => 1, 'name' => 'John', 'amount' => 345678.567], 'id');

		return (string) $column->render($item);
	}


	public function testFormat()
	{
		$number = $this->grid->addColumnNumber('amount', 'Amount');
		Assert::same('345 679', $this->render($number));

		$number = $this->grid->addColumnNumber('amount2', 'Amount', 'amount')->setFormat('2', '.', ',');
		Assert::same('345,678.57', $this->render($number));
	}

}


$test_case = new ColumnNumberTest;
$test_case->run();
