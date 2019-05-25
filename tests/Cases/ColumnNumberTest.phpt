<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\Tests\Cases;

use Tester\Assert;
use Tester\TestCase;
use Ublaboo;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/TestingDataGridFactory.php';

final class ColumnNumberTest extends TestCase
{

	/**
	 * @var Ublaboo\DataGrid\DataGrid
	 */
	private $grid;

	public function setUp(): void
	{
		$factory = new Ublaboo\DataGrid\Tests\Files\TestingDataGridFactory();
		$this->grid = $factory->createTestingDataGrid();
	}


	public function render($column)
	{
		$item = new Ublaboo\DataGrid\Row($this->grid, ['id' => 1, 'name' => 'John', 'amount' => 345678.567], 'id');

		return (string) $column->render($item);
	}


	public function testFormat(): void
	{
		$number = $this->grid->addColumnNumber('amount', 'Amount');
		Assert::same('345 679', $this->render($number));

		$number = $this->grid->addColumnNumber('amount2', 'Amount', 'amount')->setFormat(2, '.', ',');
		Assert::same('345,678.57', $this->render($number));
	}

}

(new ColumnNumberTest)->run();
