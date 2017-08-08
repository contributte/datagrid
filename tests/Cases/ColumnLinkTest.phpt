<?php

namespace Ublaboo\DataGrid\Tests\Cases;

use Tester\TestCase;
use Tester\Assert;
use Ublaboo;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/XTestingDataGridFactory.php';

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
		$item = new Ublaboo\DataGrid\Row($this->grid, ['id' => 1, 'name' => 'John'], 'id');

		return (string) $column->render($item);
	}


	public function testLink()
	{
		$link = $this->grid->addColumnLink('name', 'Href');
		Assert::same('<a href="name?id=1">John</a>', $this->render($link));

		$link = $this->grid->addColumnLink('name2', 'Href', 'edit', 'name');
		Assert::same('<a href="edit?id=1">John</a>', $this->render($link));

		$link = $this->grid->addColumnLink('name3', 'Href', 'edit', 'id');
		Assert::same('<a href="edit?id=1">1</a>', $this->render($link));

		$link = $this->grid->addColumnLink('name4', 'Href', 'edit', 'id', ['name']);
		Assert::same('<a href="edit?name=John">1</a>', $this->render($link));

		$link = $this->grid->addColumnLink('name5', 'Href', 'edit', 'id', ['name', 'id']);
		Assert::same('<a href="edit?name=John&amp;id=1">1</a>', $this->render($link));

		$link = $this->grid->addColumnLink('name6', 'Href', 'edit', 'id', [
			'name' => 'id',
			'id' => 'name'
		]);
		Assert::same('<a href="edit?name=1&amp;id=John">1</a>', $this->render($link));
	}


	public function testLinkClass()
	{
		$link = $this->grid->addColumnLink('name', 'Href')->setClass('btn');
		Assert::same('<a href="name?id=1" class="btn">John</a>', $this->render($link));

		$link->setClass(NULL);
		Assert::same('<a href="name?id=1">John</a>', $this->render($link));
	}


	public function testLinkTitle()
	{
		$link = $this->grid->addColumnLink('name', 'Href')->setTitle('Hello');
		Assert::same('<a href="name?id=1" title="Hello">John</a>', $this->render($link));
	}

}


$test_case = new ColumnLinkTest;
$test_case->run();
