<?php

namespace Ublaboo\DataGrid\Tests\Cases;

use Tester\TestCase;
use Tester\Assert;
use Ublaboo;
use Ublaboo\DataGrid\Column\ColumnLink;

require __DIR__ . '/../bootstrap.php';

final class ColumnLinkTest extends TestCase
{

	/**
	 * @var Ublaboo\DataGrid\DataGrid
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


	public function render(ColumnLink $column)
	{
		$item = new Ublaboo\DataGrid\Row($this->grid, ['id' => 1, 'name' => 'John'], 'id');

		return (string) $column->render($item);
	}


	public function testLink()
	{
		$link = $this->grid->addColumnLink('name', 'Href');
		Assert::same('<a href="' . $this->urlPrefix . '/?id=1&amp;action=name&amp;presenter=XTesting">John</a>', $this->render($link));

		$link = $this->grid->addColumnLink('name2', 'Href', 'edit', 'name');
		Assert::same('<a href="' . $this->urlPrefix . '/?id=1&amp;action=edit&amp;presenter=XTesting">John</a>', $this->render($link));

		$link = $this->grid->addColumnLink('name3', 'Href', 'edit', 'id');
		Assert::same('<a href="' . $this->urlPrefix . '/?id=1&amp;action=edit&amp;presenter=XTesting">1</a>', $this->render($link));

		$link = $this->grid->addColumnLink('name4', 'Href', 'edit', 'id', ['name']);
		Assert::same('<a href="' . $this->urlPrefix . '/?name=John&amp;action=edit&amp;presenter=XTesting">1</a>', $this->render($link));

		$link = $this->grid->addColumnLink('name5', 'Href', 'edit', 'id', ['name', 'id']);
		Assert::same('<a href="' . $this->urlPrefix . '/?name=John&amp;id=1&amp;action=edit&amp;presenter=XTesting">1</a>', $this->render($link));

		$link = $this->grid->addColumnLink('name6', 'Href', 'edit', 'id', [
			'name' => 'id',
			'id' => 'name'
		]);
		Assert::same('<a href="' . $this->urlPrefix . '/?name=1&amp;id=John&amp;action=edit&amp;presenter=XTesting">1</a>', $this->render($link));
	}


	public function testLinkClass()
	{
		$link = $this->grid->addColumnLink('name', 'Href')->setClass('btn');
		Assert::same('<a href="' . $this->urlPrefix . '/?id=1&amp;action=name&amp;presenter=XTesting" class="btn">John</a>', $this->render($link));

		$link->setClass(NULL);
		Assert::same('<a href="' . $this->urlPrefix . '/?id=1&amp;action=name&amp;presenter=XTesting">John</a>', $this->render($link));
	}


	public function testLinkTitle()
	{
		$link = $this->grid->addColumnLink('name', 'Href')->setTitle('Hello');
		Assert::same('<a href="' . $this->urlPrefix . '/?id=1&amp;action=name&amp;presenter=XTesting" title="Hello">John</a>', $this->render($link));
	}

}


$test_case = new ColumnLinkTest;
$test_case->run();
