<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases\DataSources;

use Contributte\Datagrid\DataSource\DibiFluentDataSource;
use Contributte\Datagrid\DataSource\NetteDatabaseDataSource;
use Contributte\Datagrid\DataSource\NetteDatabaseTableDataSource;
use Contributte\Datagrid\Filter\FilterDate;
use Contributte\Datagrid\Filter\FilterDateRange;
use Contributte\Datagrid\Tests\Files\DateFilterTests;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;
use DateTime;
use Dibi\Connection as DibiConnection;
use Nette\Caching\Storages\DevNullStorage;
use Nette\Database\Connection;
use Nette\Database\ConnectionException;
use Nette\Database\Conventions\StaticConventions;
use Nette\Database\Explorer;
use Nette\Database\Structure;
use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../Files/DateFilterTests.phpt';

/**
 * @dataProvider sqlDateFilter.ini
 */
final class SqlDateFilterTest extends TestCase
{

	use DateFilterTests;

	private Explorer $db;

	private string $driver;

	private string $adapter;

	public function testNativeDateAndNull(): void
	{
		$this->createDateSource([
			['id' => 1, 'created' => '2024-03-31 12:00:00'],
			['id' => 2, 'created' => '2024-04-01 00:00:00'],
			['id' => 3, 'created' => null],
		]);
		$grid = (new TestingDatagridFactory())->createTestingDatagrid();
		foreach (['created', 'only_date'] as $column) {
			$filter = new FilterDate($grid, $column, 'Date', $column);
			$filter->setFormat('Y-m-d', 'yyyy-mm-dd');
			$filter->setValue('2024-03-31');
			$source = $this->newSource();
			$source->filter([$filter]);
			Assert::same([1], $this->dateResultIds($source));
			$range = new FilterDateRange($grid, $column, 'Date', $column, 'To');
			$range->setFormat('Y-m-d', 'yyyy-mm-dd');
			$range->setValue(['from' => '', 'to' => '2024-03-31']);
			$source = $this->newSource();
			$source->filter([$range]);
			Assert::same([1], $this->dateResultIds($source));
		}
	}

	public function testLegacyComparisons(): void
	{
		$this->createDateSource([
			['id' => 1, 'created' => '2024-03-30 12:00:00'],
			['id' => 2, 'created' => '2024-03-31 12:00:00'],
			['id' => 3, 'created' => '2024-04-01 12:00:00'],
		]);
		$grid = (new TestingDatagridFactory())->createTestingDatagrid();
		$date = new FilterDate($grid, 'created', 'Date', 'created');
		$date->setFormat('Y-m-d', 'yyyy-mm-dd');
		$date->setValue('2024-03-31');
		$source = $this->newSource()->setIndexFriendlyDateFilter(false);
		$source->filter([$date]);
		Assert::same([2], $this->dateResultIds($source));
		$range = new FilterDateRange($grid, 'created', 'Date', 'created', 'To');
		$range->setFormat('Y-m-d', 'yyyy-mm-dd');
		$from = new DateTime('2024-03-30 12:34:56');
		$to = new DateTime('2024-03-31 15:16:17');
		$range->setValue(['from' => $from, 'to' => $to]);
		$source = $this->newSource()->setIndexFriendlyDateFilter(false);
		$source->filter([$range]);
		// Legacy Dibi binds a full timestamp against DATE(column); SQLite compares the text literally.
		Assert::same($this->driver === 'sqlite' && $this->adapter === 'dibi' ? [2] : [1, 2], $this->dateResultIds($source));
		Assert::same('2024-03-30 12:34:56', $from->format('Y-m-d H:i:s'));
		Assert::same('2024-03-31 15:16:17', $to->format('Y-m-d H:i:s'));
	}

	public function testOffsetTextRequiresLegacyComparison(): void
	{
		if ($this->driver !== 'sqlite') {
			return;
		}

		$this->createDateSource([
			['id' => 1, 'created' => '2024-03-31T00:30:00+02:00'],
			['id' => 2, 'created' => '2024-03-31 12:00:00'],
		]);
		$date = new FilterDate((new TestingDatagridFactory())->createTestingDatagrid(), 'created', 'Date', 'created');
		$date->setFormat('Y-m-d', 'yyyy-mm-dd');
		$date->setValue('2024-03-31');
		$source = $this->newSource();
		$source->filter([$date]);
		Assert::same([1, 2], $this->dateResultIds($source));
		$source = $this->newSource()->setIndexFriendlyDateFilter(false);
		$source->filter([$date]);
		Assert::same([2], $this->dateResultIds($source));
	}

	public function testOrdinaryIndex(): void
	{
		$rows = [];
		for ($id = 1; $id <= 200; $id++) {
			$rows[] = ['id' => $id, 'created' => $id === 1 ? '2024-03-31 12:00:00' : '2024-04-01 12:00:00'];
		}

		$source = $this->createDateSource($rows);
		$date = new FilterDate((new TestingDatagridFactory())->createTestingDatagrid(), 'created', 'Date', 'created');
		$date->setFormat('Y-m-d', 'yyyy-mm-dd');
		$date->setValue('2024-03-31');
		$source->filter([$date]);
		if ($source instanceof NetteDatabaseTableDataSource) {
			$builder = $source->getDataSource()->getSqlBuilder();
			$sql = $builder->buildSelectQuery();
			$params = $builder->getParameters();
		} elseif ($source instanceof NetteDatabaseDataSource) {
			[$sql, $params] = $source->getQuery();
		} else {
			$sql = (string) $source->getDataSource();
			$params = [];
		}

		$statement = $this->db->getConnection()->getPdo()->prepare(($this->driver === 'mysql' ? 'EXPLAIN ' : 'EXPLAIN QUERY PLAN ') . $sql);
		$statement->execute($params);
		$plan = $statement->fetchAll(\PDO::FETCH_ASSOC);
		if ($this->driver === 'mysql') {
			Assert::same('range', $plan[0]['type']);
			Assert::same('idx_datagrid_date_filter_created', $plan[0]['key']);
		} else {
			Assert::contains('SEARCH', $plan[0]['detail']);
			Assert::contains('idx_datagrid_date_filter_created', $plan[0]['detail']);
		}
	}

	protected function setUp(): void
	{
		$args = Environment::loadData();
		$this->driver = $args['driver'];
		$this->adapter = $args['adapter'];
		if ($this->driver === 'mysql') {
			if (!extension_loaded('pdo_mysql')) {
				Environment::skip('Test requires pdo_mysql.');
			}

			$port = getenv('DATAGRID_TEST_MYSQL_PORT') ?: '3306';
			try {
				$connection = new Connection('mysql:host=127.0.0.1;port=' . $port . ';dbname=tests', 'root', '');
			} catch (ConnectionException $e) {
				if (getenv('GITHUB_ACTIONS') !== false) {
					throw $e;
				}

				Environment::skip('MySQL test database is unavailable.');
			}
		} else {
			$connection = new Connection('sqlite::memory:', null, null, ['formatDateTime' => 'Y-m-d H:i:s']);
		}

		$storage = new DevNullStorage();
		$this->db = new Explorer($connection, new Structure($connection, $storage), new StaticConventions(), $storage);
	}

	protected function createDateSource(array $rows): NetteDatabaseTableDataSource|NetteDatabaseDataSource|DibiFluentDataSource
	{
		$this->db->query($this->driver === 'mysql' ? 'DROP TEMPORARY TABLE IF EXISTS datagrid_date_filter' : 'DROP TABLE IF EXISTS temp.datagrid_date_filter');
		$type = $this->driver === 'mysql' ? 'DATETIME(6)' : 'DATETIME';
		$this->db->query('CREATE TEMPORARY TABLE datagrid_date_filter (id INTEGER PRIMARY KEY, created ' . $type . ', only_date DATE)');
		$this->db->query('CREATE INDEX idx_datagrid_date_filter_created ON datagrid_date_filter(created)');
		foreach ($rows as $row) {
			$this->db->query('INSERT INTO datagrid_date_filter', $row + ['only_date' => $row['created'] === null ? null : substr($row['created'], 0, 10)]);
		}

		return $this->newSource();
	}

	private function newSource(): NetteDatabaseTableDataSource|NetteDatabaseDataSource|DibiFluentDataSource
	{
		return match ($this->adapter) {
			'table' => new NetteDatabaseTableDataSource($this->db->table('datagrid_date_filter')->select('id'), 'id'),
			'sql' => new NetteDatabaseDataSource($this->db, 'SELECT * FROM datagrid_date_filter'),
			default => $this->newDibiSource(),
		};
	}

	private function newDibiSource(): DibiFluentDataSource
	{
		$pdo = $this->db->getConnection()->getPdo();
		// Dibi requires silent mode when accepting an existing PDO connection.
		$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);
		$dibi = new DibiConnection(['driver' => 'pdo', 'resource' => $pdo]);
		$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

		return new DibiFluentDataSource($dibi->select('id')->from('datagrid_date_filter'), 'id');
	}

}

(new SqlDateFilterTest())->run();
