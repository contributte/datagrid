<?php

namespace Ublaboo\DataGrid\Tests\Cases;

use Tester\TestCase;
use Tester\Assert;
use Ublaboo\DataGrid\Column\Action;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/XTestingDataGridFactory.php';

final class ColumnActionTest extends TestCase
{

	/**
	 * @var DataGrid
	 */
	private $grid;

	/**
	 * @var string
	 */
	private $urlPrefix;


	public function setUp()
	{
		$factory = new Ublaboo\DataGrid\Tests\Files\XTestingDataGridFactory;
		$this->grid = $factory->createXTestingDataGrid();

		[$this->urlPrefix] = explode('/', $this->grid->getPresenter()->link('this'));
	}


	public function render(Action $column)
	{
		$item = new Ublaboo\DataGrid\Row($this->grid, ['id' => 1, 'name' => 'John'], 'id');

		return (string) $column->render($item);
	}


	public function testActionDuplcitColumn()
	{
		$this->grid->addAction('action', 'Do', 'doStuff!');

		$grid = $this->grid;
		$add_action = function() use ($grid) {
			$grid->addAction('action', 'Do', 'doStuff!');
		};

		Assert::exception(
			$add_action,
			'Ublaboo\DataGrid\Exception\DataGridException',
			'There is already action at key [action] defined.'
		);
	}


	public function testActionLink()
	{
		$action = $this->grid->addAction('action', 'Do', 'doStuff!');

		Assert::same(
			'<a href="' . $this->urlPrefix . '/?id=1&amp;action=default&amp;do=doStuff&amp;presenter=XTesting" class="btn btn-xs btn-default btn-secondary">Do</a>',
			$this->render($action)
		);

		$action = $this->grid->addAction('detail', 'Do');

		Assert::same(
			'<a href="' . $this->urlPrefix . '/?id=1&amp;action=detail&amp;presenter=XTesting" class="btn btn-xs btn-default btn-secondary">Do</a>',
			$this->render($action)
		);

		$action = $this->grid->addAction('title', 'Do', 'detail', ['id', 'name']);
		Assert::same(
			'<a href="' . $this->urlPrefix . '/?id=1&amp;name=John&amp;action=detail&amp;presenter=XTesting" class="btn btn-xs btn-default btn-secondary">Do</a>',
			$this->render($action)
		);

		$action = $this->grid->addAction('title2', 'Do', 'detail', [
			'id' => 'name', 'name' => 'id'
		]);
		Assert::same(
			'<a href="' . $this->urlPrefix . '/?id=John&amp;name=1&amp;action=detail&amp;presenter=XTesting" class="btn btn-xs btn-default btn-secondary">Do</a>',
			$this->render($action)
		);
	}


	public function testActionIcon()
	{
		$action = $this->grid->addAction('action', 'Do', 'doStuff!');

		DataGrid::$icon_prefix = 'icon-';
		$action->setIcon('user');

		Assert::same(
			'<a href="' . $this->urlPrefix . '/?id=1&amp;action=default&amp;do=doStuff&amp;presenter=XTesting" class="btn btn-xs btn-default btn-secondary"><span class="icon-user"></span>&nbsp;Do</a>',
			$this->render($action)
		);
	}


	public function testActionClass()
	{
		$action = $this->grid->addAction('action', 'Do', 'doStuff!')->setClass('btn');

		Assert::same('<a href="' . $this->urlPrefix . '/?id=1&amp;action=default&amp;do=doStuff&amp;presenter=XTesting" class="btn">Do</a>', $this->render($action));

		$action->setClass(NULL);

		Assert::same('<a href="' . $this->urlPrefix . '/?id=1&amp;action=default&amp;do=doStuff&amp;presenter=XTesting">Do</a>', $this->render($action));
	}


	public function testActionTitle()
	{
		$action = $this->grid->addAction('action', 'Do', 'doStuff!')->setTitle('hello');

		Assert::same(
			'<a href="' . $this->urlPrefix . '/?id=1&amp;action=default&amp;do=doStuff&amp;presenter=XTesting" title="hello" class="btn btn-xs btn-default btn-secondary">Do</a>',
			$this->render($action)
		);
	}


	public function testActionConfirm()
	{
		$action = $this->grid->addAction('action', 'Do', 'doStuff!')->setConfirm('Really?');

		Assert::same(
			'<a href="' . $this->urlPrefix . '/?id=1&amp;action=default&amp;do=doStuff&amp;presenter=XTesting" class="btn btn-xs btn-default btn-secondary" data-datagrid-confirm="Really?">Do</a>',
			$this->render($action)
		);
	}


	public function testActionRenderCondition()
	{
		$action = $this->grid->addAction('action1', 'Do', 'doStuff!')->setRenderCondition(function () {
			return true;
		});

		Assert::same('<a href="' . $this->urlPrefix . '/?id=1&amp;action=default&amp;do=doStuff&amp;presenter=XTesting" class="btn btn-xs btn-default btn-secondary">Do</a>', $this->render($action));

		$action = $this->grid->addAction('action2', 'Do', 'doStuff!')->setRenderCondition(function () {
			return false;
		});

		Assert::same('', $this->render($action));
	}

}


$test_case = new ColumnActionTest;
$test_case->run();
