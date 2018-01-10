<?php

namespace Ublaboo\DataGrid\Tests\Cases;

use Tester\TestCase;
use Tester\Assert;
use Ublaboo;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/XTestingDataGridFactory.php';

final class ColumnTranslatableTest extends TestCase
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


	public function testTranslatable()
	{
		$grid = $this->grid;

		$grid->addColumnText('translatable', 'translatable');

		$translatable = $grid->getColumn('translatable');

		Assert::same(TRUE, $translatable->isTranslatableHeader());
	}
	
	public function testDisabledTranslating()
	{
		$grid = $this->grid;

		$grid->addColumnText('non_translatable', 'non_translatable')
			->setTranslatableHeader(FALSE);

		$translatable = $grid->getColumn('non_translatable');

		Assert::same(FALSE, $translatable->isTranslatableHeader());
	}
}


$test_case = new ColumnTranslatableTest;
$test_case->run();
