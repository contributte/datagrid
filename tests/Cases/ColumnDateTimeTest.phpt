<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases;

use Contributte\Datagrid\Column\ColumnDateTime;
use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Row;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;
use DateTime;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/TestingDatagridFactory.php';

final class ColumnDateTimeTest extends TestCase
{

	private Datagrid $grid;

	public function setUp(): void
	{
		$factory = new TestingDatagridFactory();
		$this->grid = $factory->createTestingDatagrid();
	}

	public function render(ColumnDateTime $column): string
	{
		$datetime = DateTime::createFromFormat('Y-m-d H:i:s', '2015-12-15 22:58:42');
		$item = new Row($this->grid, ['id' => 1, 'name' => 'John', 'date' => $datetime], 'id');

		return $column->render($item);
	}

	public function testFormat(): void
	{
		/**
		 * Defaul forma is 'j. n. Y'
		 */
		$datetime = $this->grid->addColumnDateTime('date', 'Date');
		Assert::same('15. 12. 2015', $this->render($datetime));

		$datetime->setFormat('H:i:s');
		Assert::same('22:58:42', $this->render($datetime));
	}

}


$test_case = new ColumnDateTimeTest();
$test_case->run();
