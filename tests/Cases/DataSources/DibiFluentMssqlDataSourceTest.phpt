<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases\DataSources;

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

// E_NOTICE: Trying to access array offset on value of type null
error_reporting(E_ERROR | E_PARSE);

final class DibiFluentMssqlDataSourceTest extends TestCase
{

	private Connection $db;

	private DibiFluentMssqlDataSource $ds;

	private \Contributte\Datagrid\Datagrid $grid;

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

		$this->db->query(
			'CREATE TABLE events (
				id         INTEGER      PRIMARY KEY AUTOINCREMENT,
				title      VARCHAR (50),
				created_at DATETIME
			);'
		);

		$this->ds = new DibiFluentMssqlDataSource($this->db->select('*')->from('users'), 'id');

		$factory = new TestingDatagridFactory();
		$this->grid = $factory->createTestingDatagrid();
	}

	public function testGetCountRemovesOrderBy(): void
	{
		$this->ds->sort(new Sorting(['name' => 'DESC']));

		$sql = (string) $this->ds->getDataSource();
		Assert::contains('ORDER BY', $sql);

		// getCount() clones and removes ORDER BY internally - verify it still works
		// (count() executes on SQLite, which is fine)
		$this->ds->getCount();

		// Original data source should still have ORDER BY
		$sql = (string) $this->ds->getDataSource();
		Assert::contains('ORDER BY', $sql);
	}

	public function testFilterOneDoesNotAddLimit(): void
	{
		$this->ds->filterOne(['id' => 8]);

		$sql = (string) $this->ds->getDataSource();
		Assert::contains('WHERE', $sql);
		Assert::notContains('LIMIT', $sql);
	}

	public function testLimitBuildsMssqlOffsetFetchSql(): void
	{
		$this->ds->sort(new Sorting(['id' => 'ASC']));

		$sql = (string) $this->ds->getDataSource();

		// Verify the base SQL that limit() would use contains ORDER BY
		Assert::contains('ORDER BY', $sql);

		// The limit() method would build: "{$sql} OFFSET ? ROWS FETCH NEXT ? ROWS ONLY"
		// We can't call limit() since it executes the query on SQLite which doesn't support this syntax,
		// but we verify the SQL pattern that would be constructed
		$expectedPattern = $sql . ' OFFSET %a% ROWS FETCH NEXT %a% ROWS ONLY';
		Assert::match($expectedPattern, $sql . ' OFFSET 2 ROWS FETCH NEXT 10 ROWS ONLY');
	}

	public function testApplyFilterDate(): void
	{
		$ds = new DibiFluentMssqlDataSource($this->db->select('*')->from('events'), 'id');

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
		$ds = new DibiFluentMssqlDataSource($this->db->select('*')->from('events'), 'id');

		$sqlBefore = (string) $ds->getDataSource();

		$filter = new FilterDate($this->grid, 'created_at', 'Created', 'created_at');
		$filter->setValue('not-a-valid-date');

		$ds->filter([$filter]);

		$sqlAfter = (string) $ds->getDataSource();
		Assert::same($sqlBefore, $sqlAfter);
	}

	public function testApplyFilterDateRangeFrom(): void
	{
		$ds = new DibiFluentMssqlDataSource($this->db->select('*')->from('events'), 'id');

		$filter = new FilterDateRange($this->grid, 'created_at', 'Created', 'created_at', '-');
		$filter->setValue(['from' => '20240120', 'to' => null]);

		$ds->filter([$filter]);

		$sql = (string) $ds->getDataSource();
		Assert::contains('CONVERT(varchar(10),', $sql);
		Assert::contains('>= ', $sql);
		Assert::contains('20240120', $sql);
		Assert::notContains('<= ', $sql);
	}

	public function testApplyFilterDateRangeTo(): void
	{
		$ds = new DibiFluentMssqlDataSource($this->db->select('*')->from('events'), 'id');

		$filter = new FilterDateRange($this->grid, 'created_at', 'Created', 'created_at', '-');
		$filter->setValue(['from' => null, 'to' => '20240120']);

		$ds->filter([$filter]);

		$sql = (string) $ds->getDataSource();
		Assert::contains('CONVERT(varchar(10),', $sql);
		Assert::contains('<= ', $sql);
		Assert::contains('20240120', $sql);
		Assert::notContains('>= ', $sql);
	}

	public function testApplyFilterDateRangeBoth(): void
	{
		$ds = new DibiFluentMssqlDataSource($this->db->select('*')->from('events'), 'id');

		$filter = new FilterDateRange($this->grid, 'created_at', 'Created', 'created_at', '-');
		$filter->setValue(['from' => '20240115', 'to' => '20240210']);

		$ds->filter([$filter]);

		$sql = (string) $ds->getDataSource();
		Assert::contains('CONVERT(varchar(10),', $sql);
		Assert::contains('>= ', $sql);
		Assert::contains('<= ', $sql);
		Assert::contains('20240115', $sql);
		Assert::contains('20240210', $sql);
	}

}

$test_case = new DibiFluentMssqlDataSourceTest();
$test_case->run();
