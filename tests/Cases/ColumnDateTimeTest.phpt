<?php

namespace Ublaboo\DataGrid\Tests\Cases;

use Tester\TestCase;
use Tester\Assert;
use Ublaboo;

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
		$factory = new Ublaboo\DataGrid\Tests\Files\XTestingDataGridFactory;
		$this->grid = $factory->createXTestingDataGrid();
	}


	public function render($column)
	{
		$datetime = \DateTime::createFromFormat('Y-m-d H:i:s', '2015-12-15 22:58:42');
		$item = new Ublaboo\DataGrid\Row($this->grid, ['id' => 1, 'name' => 'John', 'date' => $datetime], 'id');

		return (string) $column->render($item);
	}


	public function testFormat()
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


$test_case = new ColumnDateTimeTest;
$test_case->run();
