<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases;

use Contributte\Datagrid\Column\ColumnNumber;
use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Row;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;
use Mockery;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/TestingDatagridFactory.php';

final class ColumnNumberBigIntegerTest extends TestCase
{

	private Datagrid $grid;

	public function setUp(): void
	{
		$factory = new TestingDatagridFactory();
		$this->grid = $factory->createTestingDatagrid();
	}

	public function render(ColumnNumber $column): string
	{
		$item = new Row($this->grid, ['id' => 1, 'name' => 'John', 'amount' => 1234567891234567891], 'id');

		return $column->render($item);
	}

	public function testFormat(): void
	{
		$number = $this->grid->addColumnNumber('amount', 'Amount');
		Assert::same('1 234 567 891 234 567 891', $this->render($number));

		$number = $this->grid->addColumnNumber('amount2', 'Amount', 'amount')->setFormat(2, '.', ',');
		Assert::same('1,234,567,891,234,567,891.00', $this->render($number));
	}

	public function testNumberIsNotParsedWhenSafeFloatRange(): void
	{
		$smallInteger = 123;
		$row = Mockery::mock(Row::class);
		$column = Mockery::mock(ColumnNumber::class, [$this->grid, 'id', 'amount', 'Amount'])
			->makePartial();

		$row->shouldReceive('getValue')
			->andReturn($smallInteger);

		$column->shouldAllowMockingProtectedMethods();
		$column->shouldNotReceive('parseBigIntNumber');

		Assert::notSame('parsed', $column->getColumnValue($row));
		Assert::same((string) $smallInteger, $column->getColumnValue($row));
	}

	public function testNumberIsParsedWhenOutOfSafeFloatRange(): void
	{
		$bigInteger = 1234567891234567891;
		$row = Mockery::mock(Row::class);
		$column = Mockery::mock(ColumnNumber::class, [$this->grid, 'id', 'amount', 'Amount'])
			->makePartial();

		$row->shouldReceive('getValue')
			->once()
			->andReturn($bigInteger);

		$column->shouldAllowMockingProtectedMethods();
		$column->shouldReceive('parseBigIntNumber')
			->once()
			->with((string) $bigInteger)
			->andReturn('parsed');

		Assert::same('parsed', $column->getColumnValue($row));
	}

}

(new ColumnNumberBigIntegerTest())->run();
