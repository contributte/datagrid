<?php

namespace Ublaboo\DataGrid\Tests\Cases;

use Tester\TestCase,
	Tester\Assert,
	Ublaboo;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../files/XTestingDataGridFactory.php';

final class ColumnTextTest extends TestCase
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


	public function render($column, $id = 1)
	{
		if ($id == 1) {
			$item = ['id' => 1, 'name' => 'John'];
		} else {
			$item = ['id' => $id, 'name' => 'Susie'];
		}

		return (string) $column->render($item);
	}


	public function testSimpleOutput()
	{
		$text = $this->grid->addColumnText('name', 'Name');
		Assert::same('John', $this->render($text));
	}


	public function testReplacement()
	{
		$text = $this->grid->addColumnText('name', 'Name')
			->setReplacement(['John' => 'Joe']);

		Assert::same('Joe', $this->render($text));
		Assert::same('Susie', $this->render($text, 2));
	}


	public function testRenderer()
	{
		$text = $this->grid->addColumnText('name', 'Name')
			->setRenderer(function($item) {
				return str_repeat($item['name'], 2);
			});

		Assert::same('JohnJohn', $this->render($text));
	}

}


$test_case = new ColumnTextTest;
$test_case->run();
