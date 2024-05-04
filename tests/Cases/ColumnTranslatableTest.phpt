<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases;

use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/TestingDatagridFactory.php';

final class ColumnTranslatableTest extends TestCase
{

	private Datagrid $datagrid;

	public function setUp(): void
	{
		$factory = new TestingDatagridFactory();
		$this->datagrid = $factory->createTestingDatagrid();
	}

	public function testTranslatable(): void
	{
		$datagrid = $this->datagrid;

		$datagrid->addColumnText('translatable', 'translatable');

		$translatable = $datagrid->getColumn('translatable');

		Assert::same(true, $translatable->isTranslatableHeader());
	}

	public function testDisabledTranslating(): void
	{
		$datagrid = $this->datagrid;

		$datagrid->addColumnText('non_translatable', 'non_translatable')
			->setTranslatableHeader(false);

		$translatable = $datagrid->getColumn('non_translatable');

		Assert::same(false, $translatable->isTranslatableHeader());
	}

}


$test_case = new ColumnTranslatableTest();
$test_case->run();
