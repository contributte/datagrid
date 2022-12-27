<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Tests\Cases;

use DateTime;
use Tester\Assert;
use Tester\TestCase;
use Ublaboo\DataGrid\Column\ColumnDateTime;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Row;
use Ublaboo\DataGrid\Tests\Files\TestingDataGridFactory;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/TestingDataGridFactory.php';

final class ColumnDateTimeTest extends TestCase
{

	private DataGrid $grid;

	public function setUp(): void
	{
		$factory = new TestingDataGridFactory();
		$this->grid = $factory->createTestingDataGrid();
	}

	public function render(ColumnDateTime $column): string
	{
		$datetime = DateTime::createFromFormat('Y-m-d H:i:s', '2015-12-15 22:58:42');
		$item = new Row($this->grid, ['id' => 1, 'name' => 'John', 'date' => $datetime], 'id');

		return $column->render($item);
	}

	public function testFormat(): void
	{
		/**
		 * Defaul forma is 'j. n. Y'
		 */
		$datetime = $this->grid->addColumnDateTime('date', 'Date');
		Assert::same('15. 12. 2015', $this->render($datetime));

		$datetime->setFormat('H:i:s');
		Assert::same('22:58:42', $this->render($datetime));
	}

}


$test_case = new ColumnDateTimeTest();
$test_case->run();
