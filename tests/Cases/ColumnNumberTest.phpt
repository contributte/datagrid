<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases;

use Contributte\Datagrid\Column\ColumnNumber;
use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Row;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/TestingDatagridFactory.php';

final class ColumnNumberTest extends TestCase
{

	private Datagrid $grid;

	public function setUp(): void
	{
		$factory = new TestingDatagridFactory();
		$this->grid = $factory->createTestingDatagrid();
	}

	public function render(ColumnNumber $column): string
	{
		$item = new Row($this->grid, ['id' => 1, 'name' => 'John', 'amount' => 345678.567], 'id');

		return $column->render($item);
	}

	public function testFormat(): void
	{
		$number = $this->grid->addColumnNumber('amount', 'Amount');
		Assert::same('345 679', $this->render($number));

		$number = $this->grid->addColumnNumber('amount2', 'Amount', 'amount')->setFormat(2, '.', ',');
		Assert::same('345,678.57', $this->render($number));
	}

}

(new ColumnNumberTest())->run();
