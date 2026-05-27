<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases\DataSources;

use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\DataSource\DibiFluentMssqlDataSource;
use Contributte\Datagrid\Filter\FilterDate;
use Contributte\Datagrid\Filter\FilterDateRange;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;
use Contributte\Datagrid\Utils\Sorting;
use Dibi\Connection;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';
require __DIR__ . '/../../Files/TestingDatagridFactory.php';

error_reporting(E_ERROR | E_PARSE);

final class DibiFluentMssqlDataSourceTest extends TestCase
{

	private Connection $db;

	private Datagrid $grid;

	protected function setUp(): void
	{
		$this->db = new Connection([
			'driver' => 'pdo',
			'dsn' => 'sqlite::memory:',
		]);

		$this->db->query(
			'CREATE TABLE users (
				id      INTEGER      PRIMARY KEY AUTOINCREMENT,
				name    VARCHAR (50),
				age     INTEGER (3),
				address VARCHAR (50)
			);'
		);

		$this->grid = (new TestingDatagridFactory())->createTestingDatagrid();
	}

	public function testGetCountRemovesOrderBy(): void
	{
		$ds = $this->createDataSource();
		$ds->sort(new Sorting(['name' => 'DESC']));

		Assert::contains('ORDER BY', (string) $ds->getDataSource());

		$ds->getCount();

		Assert::contains('ORDER BY', (string) $ds->getDataSource());
	}

	public function testFilterOneDoesNotAddLimit(): void
	{
		$ds = $this->createDataSource();
		$ds->filterOne(['id' => 8]);

		$sql = (string) $ds->getDataSource();
		Assert::contains('WHERE', $sql);
		Assert::notContains('LIMIT', $sql);
	}

	public function testLimitSqlContainsOffsetFetch(): void
	{
		$ds = $this->createDataSource();
		$ds->sort(new Sorting(['id' => 'ASC']));

		$sql = (string) $ds->getDataSource();
		Assert::contains('SELECT', $sql);
		Assert::contains('ORDER BY', $sql);
	}

	public function testApplyFilterDate(): void
	{
		$ds = $this->createDataSource();

		$filter = new FilterDate($this->grid, 'created_at', 'Created', 'created_at');
		$filter->setValue('15. 1. 2024');
		$ds->filter([$filter]);

		$sql = (string) $ds->getDataSource();
		Assert::contains('CONVERT(varchar(10),', $sql);
		Assert::contains(', 112)', $sql);
		Assert::contains('20240115', $sql);
	}

	public function testApplyFilterDateWithInvalidValue(): void
	{
		$ds = $this->createDataSource();
		$sqlBefore = (string) $ds->getDataSource();

		$filter = new FilterDate($this->grid, 'created_at', 'Created', 'created_at');
		$filter->setValue('not-a-valid-date');
		$ds->filter([$filter]);

		Assert::same($sqlBefore, (string) $ds->getDataSource());
	}

	public function testApplyFilterDateRangeFrom(): void
	{
		$ds = $this->createDataSource();

		$filter = new FilterDateRange($this->grid, 'created_at', 'Created', 'created_at', '-');
		$filter->setValue(['from' => '20240120', 'to' => null]);
		$ds->filter([$filter]);

		$sql = (string) $ds->getDataSource();
		Assert::contains('CONVERT(varchar(10),', $sql);
		Assert::contains('>= ', $sql);
		Assert::notContains('<= ', $sql);
	}

	public function testApplyFilterDateRangeTo(): void
	{
		$ds = $this->createDataSource();

		$filter = new FilterDateRange($this->grid, 'created_at', 'Created', 'created_at', '-');
		$filter->setValue(['from' => null, 'to' => '20240120']);
		$ds->filter([$filter]);

		$sql = (string) $ds->getDataSource();
		Assert::contains('CONVERT(varchar(10),', $sql);
		Assert::contains('<= ', $sql);
		Assert::notContains('>= ', $sql);
	}

	public function testApplyFilterDateRangeBoth(): void
	{
		$ds = $this->createDataSource();

		$filter = new FilterDateRange($this->grid, 'created_at', 'Created', 'created_at', '-');
		$filter->setValue(['from' => '20240115', 'to' => '20240210']);
		$ds->filter([$filter]);

		$sql = (string) $ds->getDataSource();
		Assert::contains('>= ', $sql);
		Assert::contains('<= ', $sql);
		Assert::contains('20240115', $sql);
		Assert::contains('20240210', $sql);
	}

	private function createDataSource(): DibiFluentMssqlDataSource
	{
		return new DibiFluentMssqlDataSource($this->db->select('*')->from('users'), 'id');
	}

}

$test_case = new DibiFluentMssqlDataSourceTest();
$test_case->run();
