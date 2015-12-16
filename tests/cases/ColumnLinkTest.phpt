<?php

namespace Ublaboo\DataGrid\Tests\Cases;

use Tester\TestCase,
	Tester\Assert,
	Ublaboo;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../files/XTestingDataGridFactory.php';

final class ColumnLinkTest extends TestCase
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
		$item = ['id' => 1, 'name' => 'John'];

		return (string) $column->render($item);
	}


	public function testLink()
	{
		$link = $this->grid->addColumnLink('name', 'Href');
		Assert::same('<a href="name?id=1">John</a>', $this->render($link));

		$link = $this->grid->addColumnLink('name2', 'Href', 'name');
		Assert::same('<a href="name2?id=1">John</a>', $this->render($link));

		$link = $this->grid->addColumnLink('name3', 'Href', 'id', 'edit');
		Assert::same('<a href="edit?id=1">1</a>', $this->render($link));

		$link = $this->grid->addColumnLink('name4', 'Href', 'id', 'edit', ['name']);
		Assert::same('<a href="edit?name=John">1</a>', $this->render($link));

		$link = $this->grid->addColumnLink('name5', 'Href', 'id', 'edit', ['name', 'id']);
		Assert::same('<a href="edit?name=John&amp;id=1">1</a>', $this->render($link));

		$link = $this->grid->addColumnLink('name6', 'Href', 'id', 'edit', [
			'name' => 'id',
			'id' => 'name'
		]);
		Assert::same('<a href="edit?name=1&amp;id=John">1</a>', $this->render($link));
	}


	public function testLinkClass()
	{
		$link = $this->grid->addColumnLink('name', 'Href')->class('btn');
		Assert::same('<a href="name?id=1" class="btn">John</a>', $this->render($link));

		$link->setClass(NULL);
		Assert::same('<a href="name?id=1">John</a>', $this->render($link));
	}


	public function testLinkTitle()
	{
		$link = $this->grid->addColumnLink('name', 'Href')->title('Hello');
		Assert::same('<a href="name?id=1" title="Hello">John</a>', $this->render($link));
	}

}


$test_case = new ColumnLinkTest;
$test_case->run();
