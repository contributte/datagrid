<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases;

use Contributte\Datagrid\Column\Action;
use Contributte\Datagrid\Column\Action\Confirmation\StringConfirmation;
use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Row;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/TestingDatagridFactory.php';

final class ColumnActionTest extends TestCase
{

	private Datagrid $grid;

	public function setUp(): void
	{
		$factory = new TestingDatagridFactory();
		$this->grid = $factory->createTestingDatagrid();
	}

	public function render(Action $column): string
	{
		$item = new Row($this->grid, ['id' => 1, 'name' => 'John'], 'id');

		return (string) $column->render($item);
	}

	public function testActionDuplicityColumn(): void
	{
		$this->grid->addAction('action', 'Do', 'doStuff!');

		$grid = $this->grid;
		$add_action = function () use ($grid): void {
			$grid->addAction('action', 'Do', 'doStuff!');
		};

		Assert::exception($add_action, 'Contributte\Datagrid\Exception\DatagridException', 'There is already action at key [action] defined.');
	}

	public function testActionLink(): void
	{
		$action = $this->grid->addAction('action', 'Do', 'doStuff!');

		Assert::same(
			'<a href="doStuff!?id=1" class="btn btn-xs btn-default btn-secondary">Do</a>',
			$this->render($action)
		);

		$action = $this->grid->addAction('detail', 'Do');

		Assert::same(
			'<a href="detail?id=1" class="btn btn-xs btn-default btn-secondary">Do</a>',
			$this->render($action)
		);

		$action = $this->grid->addAction('title', 'Do', 'detail', ['id', 'name']);
		Assert::same(
			'<a href="detail?id=1&amp;name=John" class="btn btn-xs btn-default btn-secondary">Do</a>',
			$this->render($action)
		);

		$action = $this->grid->addAction('title2', 'Do', 'detail', [
			'id' => 'name',
		'name' => 'id',
		]);
		Assert::same(
			'<a href="detail?id=John&amp;name=1" class="btn btn-xs btn-default btn-secondary">Do</a>',
			$this->render($action)
		);
	}

	public function testActionIcon(): void
	{
		$action = $this->grid->addAction('action', 'Do', 'doStuff!');

		Datagrid::$iconPrefix = 'icon-';
		$action->setIcon('user');

		Assert::same(
			'<a href="doStuff!?id=1" class="btn btn-xs btn-default btn-secondary"><span class="icon-user"></span>&nbsp;Do</a>',
			$this->render($action)
		);
	}

	public function testActionClass(): void
	{
		$action = $this->grid->addAction('action', 'Do', 'doStuff!')->setClass('btn');

		Assert::same('<a href="doStuff!?id=1" class="btn">Do</a>', $this->render($action));

		$action->setClass(null);

		Assert::same('<a href="doStuff!?id=1">Do</a>', $this->render($action));
	}

	public function testActionTitle(): void
	{
		$action = $this->grid->addAction('action', 'Do', 'doStuff!')->setTitle('hello');

		Assert::same(
			'<a href="doStuff!?id=1" title="hello" class="btn btn-xs btn-default btn-secondary">Do</a>',
			$this->render($action)
		);
	}

	public function testActionCustomHref(): void
	{
		$action = $this->grid->addAction('action1', 'Do')->setCustomHref('https://www.example.com/');

		Assert::same(
			'<a href="https://www.example.com/" class="btn btn-xs btn-default btn-secondary">Do</a>',
			$this->render($action)
		);

		$action = $this->grid->addAction('action2', 'Do')->setCustomHref(fn ($rowItem) => 'https://www.example.com/?name=' . $rowItem['name']);

		Assert::same(
			'<a href="https://www.example.com/?name=John" class="btn btn-xs btn-default btn-secondary">Do</a>',
			$this->render($action)
		);

		$action = $this->grid->addAction('action3', 'Do')->setCustomHref(fn ($rowItem) => '/preview/user/?id=' . $rowItem['id']);

		Assert::same(
			'<a href="/preview/user/?id=1" class="btn btn-xs btn-default btn-secondary">Do</a>',
			$this->render($action)
		);
	}

	public function testActionConfirm(): void
	{
		$action = $this->grid->addAction('action', 'Do', 'doStuff!')
			->setConfirmation(new StringConfirmation('Really?'));

		Assert::same(
			'<a href="doStuff!?id=1" class="btn btn-xs btn-default btn-secondary" data-datagrid-confirm="Really?">Do</a>',
			$this->render($action)
		);
	}

	public function testActionRenderCondition(): void
	{
		$action = $this->grid->addAction('action1', 'Do', 'doStuff!')->setRenderCondition(fn () => true);

		Assert::same('<a href="doStuff!?id=1" class="btn btn-xs btn-default btn-secondary">Do</a>', $this->render($action));

		$action = $this->grid->addAction('action2', 'Do', 'doStuff!')->setRenderCondition(fn () => false);

		Assert::same('', $this->render($action));
	}

}


$test_case = new ColumnActionTest();
$test_case->run();
