<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Tests\Cases;

use Tester\Assert;
use Tester\TestCase;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Tests\Files\TestingDataGridFactory;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/TestingDataGridFactory.php';

final class ColumnTranslatableTest extends TestCase
{

	private DataGrid $grid;

	public function setUp(): void
	{
		$factory = new TestingDataGridFactory();
		$this->grid = $factory->createTestingDataGrid();
	}

	public function testTranslatable(): void
	{
		$grid = $this->grid;

		$grid->addColumnText('translatable', 'translatable');

		$translatable = $grid->getColumn('translatable');

		Assert::same(true, $translatable->isTranslatableHeader());
	}

	public function testDisabledTranslating(): void
	{
		$grid = $this->grid;

		$grid->addColumnText('non_translatable', 'non_translatable')
			->setTranslatableHeader(false);

		$translatable = $grid->getColumn('non_translatable');

		Assert::same(false, $translatable->isTranslatableHeader());
	}

}


$test_case = new ColumnTranslatableTest();
$test_case->run();
