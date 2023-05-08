<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases;

use Contributte\Datagrid\Column\ColumnLink;
use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Row;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/TestingDatagridFactory.php';

final class ColumnLinkTest extends TestCase
{

	private Datagrid $grid;

	public function setUp(): void
	{
		$factory = new TestingDatagridFactory();
		$this->grid = $factory->createTestingDatagrid();
	}

	public function render(ColumnLink $column): string
	{
		$item = new Row($this->grid, ['id' => 1, 'name' => 'John'], 'id');

		return (string) $column->render($item);
	}

	public function testLink(): void
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
			'id' => 'name',
		]);
		Assert::same('<a href="edit?name=1&amp;id=John">1</a>', $this->render($link));
	}

	public function testLinkClass(): void
	{
		$link = $this->grid->addColumnLink('name', 'Href')->setClass('btn');
		Assert::same('<a href="name?id=1" class="btn">John</a>', $this->render($link));

		$link->setClass(null);
		Assert::same('<a href="name?id=1">John</a>', $this->render($link));
	}

	public function testLinkTitle(): void
	{
		$link = $this->grid->addColumnLink('name', 'Href')->setTitle('Hello');
		Assert::same('<a href="name?id=1" title="Hello">John</a>', $this->render($link));
	}

}


$test_case = new ColumnLinkTest();
$test_case->run();
