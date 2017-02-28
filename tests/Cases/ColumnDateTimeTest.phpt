<?php

namespace Ublaboo\DataGrid\Tests\Cases;

use DateTime;
use Tester\TestCase;
use Tester\Assert;
use Ublaboo\DataGrid\Column\Column;
use Ublaboo\DataGrid\Tests\Files\XTestingDataGridFactory;
use Ublaboo\DataGrid\Row;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/XTestingDataGridFactory.php';

final class ColumnDateTimeTest extends TestCase
{
	/**
	 * @var Ublaboo\DataGrid\DataGrid
	 */
	private $grid;


	public function setUp()
	{
		$this->grid = (new XTestingDataGridFactory)->createXTestingDataGrid();
	}


	/**
	 * @param  Column  $column
	 * @return string
	 */
	public function render(Column $column) : string
	{
		$datetime = DateTime::createFromFormat('Y-m-d H:i:s', '2015-12-15 22:58:42');
		$params = [
			'id' => 1,
			'name' => 'John',
			'date' => $datetime
		];

		$item = new Row($this->grid, $params, 'id');
		return (string) $column->render($item);
	}


	public function testFormat()
	{
		/**
		 * Defaul format is 'j. n. Y'
		 */
		$datetime = $this->grid->addColumnDateTime('date', 'Date');
		Assert::same('15. 12. 2015', $this->render($datetime));

		$datetime->setFormat('H:i:s');
		Assert::same('22:58:42', $this->render($datetime));
	}
}


$test_case = new ColumnDateTimeTest;
$test_case->run();
