<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\Tests\Cases;

use Tester\Assert;
use Tester\TestCase;
use Tracy\Debugger;
use Ublaboo;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/TestingDataGridFactory.php';

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
			'age' => 20,
		],
		[
			'id' => 2,
			'name' => 'Susie',
			'age' => 23,
		],
		[
			'id' => 3,
			'name' => 'Alexa',
			'age' => 19,
		],
		[
			'id' => 4,
			'name' => 'Alex',
			'age' => 22,
		],
	];

	public function setUp(): void
	{
		$factory = new Ublaboo\DataGrid\Tests\Files\TestingDataGridFactory();
		$this->grid = $factory->createTestingDataGrid();
	}


	public function testExportNotFiltered(): void
	{
		$data = $this->data;
		$callback = function ($source) use ($data): void {
			Assert::same($data, $source);
		};

		$this->grid->addExportCallback('Export', $callback);

		$this->grid;
		$trigger = function (): void {
			$this->grid->handleExport(1);
		};

		Assert::exception($trigger, 'Ublaboo\DataGrid\Exception\DataGridException', 'You have to set a data source first.');

		$this->grid->setDataSource($this->data);

		$this->grid->handleExport(1);
	}



	public function testExportFiltered(): void
	{
		$data = $this->data;
		$callback = function ($source) use ($data): void {
			Assert::same($data, $source);
		};

		$this->grid->addExportCallback('Export', $callback, true);

		$this->grid->addFilterText('name', 'Name');

		$this->grid;
		$trigger = function (): void {
			$this->grid->handleExport(1);
		};

		Assert::exception($trigger, 'Ublaboo\DataGrid\Exception\DataGridException', 'You have to set a data source first.');

		$this->grid->setDataSource($this->data);

		$this->grid->handleExport(1);
	}

}

Debugger::enable();

$test_case = new ExportTest();
$test_case->run();
