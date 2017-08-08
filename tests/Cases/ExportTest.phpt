<?php

namespace Ublaboo\DataGrid\Tests\Cases;

use Tester\TestCase;
use Tester\Assert;
use Ublaboo;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/XTestingDataGridFactory.php';

final class ExportTest extends TestCase
{

	/**
	 * @var DataGrid
	 */
	private $grid;

	/**
	 * @var array
	 */
	private $data = [
		[
			'id' => 1,
			'name' => 'John Doe',
			'age' => 20
		],
		[
			'id' => 2,
			'name' => 'Susie',
			'age' => 23
		],
		[
			'id' => 3,
			'name' => 'Alexa',
			'age' => 19
		],
		[
			'id' => 4,
			'name' => 'Alex',
			'age' => 22
		]
	];


	public function setUp()
	{
		$factory = new Ublaboo\DataGrid\Tests\Files\XTestingDataGridFactory;
		$this->grid = $factory->createXTestingDataGrid();
	}


	public function testExportNotFiltered()
	{
		$data = $this->data;
		$callback = function($source) use ($data) {
			Assert::same($data, $source);
		};

		$export = $this->grid->addExportCallback('Export', $callback);

		$grid = $this->grid;
		$trigger = function() use ($grid) {
			$this->grid->handleExport(1);
		};

		Assert::exception(
			$trigger,
			'Ublaboo\DataGrid\Exception\DataGridException',
			'You have to set a data source first.'
		);

		$this->grid->setDataSource($this->data);

		$this->grid->handleExport(1);
	}



	public function testExportFiltered()
	{
		$data = $this->data;
		$callback = function($source) use ($data) {
			Assert::same($data, $source);
		};

		$export = $this->grid->addExportCallback('Export', $callback, TRUE);

		$this->grid->addFilterText('name', 'Name');

		$grid = $this->grid;
		$trigger = function() use ($grid) {
			$this->grid->handleExport(1);
		};

		Assert::exception(
			$trigger,
			'Ublaboo\DataGrid\Exception\DataGridException',
			'You have to set a data source first.'
		);

		$this->grid->setDataSource($this->data);

		$this->grid->handleExport(1);
	}

}

\Tracy\Debugger::enable();

$test_case = new ExportTest;
$test_case->run();
