<?php

namespace Ublaboo\DataGrid\Tests\Cases;

use Tester\TestCase,
	Tester\Assert,
	Mockery,
	Ublaboo\DataGrid\DataGrid,
	Ublaboo;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../files/XTestingDataGridFactory.php';

final class ColumnActionTest extends TestCase
{

	/**
	 * @var DataGrid
	 */
	private $grid;


	public function setUp()
	{
		$factory = new Ublaboo\DataGrid\Tests\Files\XTestingDataGridFactory;
		$this->grid = $factory->createXTestingDataGrid();
	}


	public function render($action, $item = ['id' => 1])
	{
		return (string) $action->render($item);
	}


	public function testActionLink()
	{
		$action = $this->grid->addAction('action', 'Do', 'doStuff!');

		Assert::same(
			'<a href="doStuff!" class="btn btn-xs btn-default">Do</a>',
			$this->render($action)
		);

		$action = $this->grid->addAction('detail', 'Do');

		Assert::same(
			'<a href="detail" class="btn btn-xs btn-default">Do</a>',
			$this->render($action)
		);
	}


	public function testActionIcon()
	{
		$action = $this->grid->addAction('action', 'Do', 'doStuff!');

		DataGrid::$icon_prefix = 'icon-';
		$action->icon('user');

		Assert::same(
			'<a href="doStuff!" class="btn btn-xs btn-default"><span class="icon-user"></span>&nbsp;Do</a>',
			$this->render($action)
		);
	}


	public function testActionClass()
	{
		$action = $this->grid->addAction('action', 'Do', 'doStuff!')->class('btn');

		Assert::same('<a href="doStuff!" class="btn">Do</a>', $this->render($action));

		$action->setClass(NULL);

		Assert::same('<a href="doStuff!">Do</a>', $this->render($action));
	}


	public function testActionTitle()
	{
		$action = $this->grid->addAction('action', 'Do', 'doStuff!')->title('hello');

		Assert::same(
			'<a href="doStuff!" title="hello" class="btn btn-xs btn-default">Do</a>',
			$this->render($action)
		);
	}


	public function testActionConfirm()
	{
		$action = $this->grid->addAction('action', 'Do', 'doStuff!')->confirm('Really?');

		Assert::same(
			'<a href="doStuff!" class="btn btn-xs btn-default" data-confirm="Really?">Do</a>',
			$this->render($action)
		);
	}

}


$test_case = new ColumnActionTest;
$test_case->run();
