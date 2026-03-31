<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases\DataSources;

use Contributte\Datagrid\DataSource\DibiFluentMssqlDataSource;
use Contributte\Datagrid\Filter\FilterDate;
use Contributte\Datagrid\Filter\FilterDateRange;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;
use Contributte\Datagrid\Utils\Sorting;
use Dibi\Connection;
use Tester\Assert;
use Tester\Environment;

require __DIR__ . '/BaseDataSourceTest.phpt';

if (!extension_loaded('sqlsrv') && !extension_loaded('pdo_sqlsrv')) {
	Environment::skip('Test requires sqlsrv or pdo_sqlsrv extension to be loaded.');
}

/**
 * @dataProvider mssqlDatasource.ini
 */
final class DibiFluentMssqlDataSourceTest extends BaseDataSourceTest
{

	protected array $data = [
		['id' => 1, 'name' => 'John Doe', 'age' => 30, 'address' => 'Blue Village 1'],
		['id' => 2, 'name' => 'Frank Frank', 'age' => 60, 'address' => 'Yellow Garded 126'],
		['id' => 3, 'name' => 'Santa Claus', 'age' => 12, 'address' => 'New York'],
		['id' => 8, 'name' => 'Jude Law', 'age' => 8, 'address' => 'Lubababa 5'],
		['id' => 30, 'name' => 'Jackie Blue', 'age' => 80, 'address' => 'Prague 678'],
		['id' => 40, 'name' => 'John Red', 'age' => 40, 'address' => 'Porto 53'],
	];

	private Connection $db;

	public function setUp(): void
	{
		$this->setUpDatabase();
		$this->ds = new DibiFluentMssqlDataSource(
			$this->db->select('*')->from('users'),
			'id'
		);
		$factory = new TestingDatagridFactory();
		$this->grid = $factory->createTestingDatagrid();
	}

	/**
	 * MSSQL OFFSET/FETCH requires ORDER BY, so we override
	 * the base test to sort before limiting.
	 */
	public function testLimit(): void
	{
		$this->ds->sort(new Sorting(['id' => 'ASC']));
		$this->ds->limit(2, 2);
		$result = $this->getActualResultAsArray();

		Assert::equal([
			$this->data[2],
			$this->data[3],
		], $result);
	}

	public function testGetCountWithOrderBy(): void
	{
		$this->ds->sort(new Sorting(['name' => 'DESC']));

		Assert::same(6, $this->ds->getCount());
	}

	public function testFilterDate(): void
	{
		$this->db->query('DROP TABLE IF EXISTS events');
		$this->db->query(
			"CREATE TABLE events (
				id INT IDENTITY(1,1) PRIMARY KEY,
				title NVARCHAR(50),
				created_at DATETIME
			)"
		);

		$this->db->query("INSERT INTO events (title, created_at) VALUES ('Event 1', '2024-01-15 10:30:00')");
		$this->db->query("INSERT INTO events (title, created_at) VALUES ('Event 2', '2024-02-20 14:00:00')");
		$this->db->query("INSERT INTO events (title, created_at) VALUES ('Event 3', '2024-01-15 18:45:00')");

		$ds = new DibiFluentMssqlDataSource(
			$this->db->select('*')->from('events'),
			'id'
		);

		$filter = new FilterDate($this->grid, 'created_at', 'Created', 'created_at');
		$filter->setValue('15. 1. 2024');
		$ds->filter([$filter]);

		Assert::same(2, $ds->getCount());
	}

	public function testFilterDateRange(): void
	{
		$this->db->query('DROP TABLE IF EXISTS events');
		$this->db->query(
			"CREATE TABLE events (
				id INT IDENTITY(1,1) PRIMARY KEY,
				title NVARCHAR(50),
				created_at DATETIME
			)"
		);

		$this->db->query("INSERT INTO events (title, created_at) VALUES ('Event 1', '2024-01-10 10:00:00')");
		$this->db->query("INSERT INTO events (title, created_at) VALUES ('Event 2', '2024-01-20 14:00:00')");
		$this->db->query("INSERT INTO events (title, created_at) VALUES ('Event 3', '2024-02-05 18:00:00')");
		$this->db->query("INSERT INTO events (title, created_at) VALUES ('Event 4', '2024-03-01 09:00:00')");

		$ds = new DibiFluentMssqlDataSource(
			$this->db->select('*')->from('events'),
			'id'
		);

		// Test "from" only
		$filter = new FilterDateRange($this->grid, 'created_at', 'Created', 'created_at', '-');
		$filter->setValue(['from' => '20240120', 'to' => null]);
		$ds->filter([$filter]);

		Assert::same(3, $ds->getCount());

		// Test "to" only
		$ds = new DibiFluentMssqlDataSource(
			$this->db->select('*')->from('events'),
			'id'
		);

		$filter = new FilterDateRange($this->grid, 'created_at', 'Created', 'created_at', '-');
		$filter->setValue(['from' => null, 'to' => '20240120']);
		$ds->filter([$filter]);

		Assert::same(2, $ds->getCount());

		// Test both "from" and "to"
		$ds = new DibiFluentMssqlDataSource(
			$this->db->select('*')->from('events'),
			'id'
		);

		$filter = new FilterDateRange($this->grid, 'created_at', 'Created', 'created_at', '-');
		$filter->setValue(['from' => '20240115', 'to' => '20240210']);
		$ds->filter([$filter]);

		Assert::same(2, $ds->getCount());
	}

	public function testFilterOneWithoutLimit(): void
	{
		$this->ds->filterOne(['id' => 8]);

		$result = $this->getActualResultAsArray();

		Assert::equal([$this->data[3]], $result);
	}

	protected function setUpDatabase(): void
	{
		$args = Environment::loadData();

		try {
			if (extension_loaded('sqlsrv')) {
				$this->db = new Connection([
					'driver' => 'sqlsrv',
					'host' => $args['host'],
					'username' => $args['username'],
					'password' => $args['password'],
					'database' => 'master',
				]);
			} else {
				$this->db = new Connection([
					'driver' => 'pdo',
					'dsn' => sprintf(
						'sqlsrv:Server=%s;Database=master;TrustServerCertificate=yes',
						$args['host']
					),
					'username' => $args['username'],
					'password' => $args['password'],
				]);
			}
		} catch (\Throwable) {
			Environment::skip('Cannot connect to MSSQL server.');
		}

		$this->db->query("IF DB_ID('tests') IS NULL CREATE DATABASE tests");

		if (extension_loaded('sqlsrv')) {
			$this->db = new Connection([
				'driver' => 'sqlsrv',
				'host' => $args['host'],
				'username' => $args['username'],
				'password' => $args['password'],
				'database' => $args['database'],
			]);
		} else {
			$this->db = new Connection([
				'driver' => 'pdo',
				'dsn' => sprintf(
					'sqlsrv:Server=%s;Database=%s;TrustServerCertificate=yes',
					$args['host'],
					$args['database']
				),
				'username' => $args['username'],
				'password' => $args['password'],
			]);
		}

		$this->db->query('DROP TABLE IF EXISTS events');
		$this->db->query('DROP TABLE IF EXISTS users');
		$this->db->query(
			"CREATE TABLE users (
				id INT NOT NULL PRIMARY KEY,
				name NVARCHAR(50),
				age INT,
				address NVARCHAR(50)
			)"
		);

		foreach ($this->data as $row) {
			$this->db->insert('users', $row)->execute();
		}
	}

}

$test_case = new DibiFluentMssqlDataSourceTest();
$test_case->run();
