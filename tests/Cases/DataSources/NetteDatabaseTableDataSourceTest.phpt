<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases\DataSources;

use Contributte\Datagrid\DataSource\NetteDatabaseTableDataSource;
use Contributte\Datagrid\Filter\FilterDate;
use Contributte\Datagrid\Filter\FilterDateRange;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;
use DateTime;
use DateTimeZone;
use Nette\Caching\Storages\DevNullStorage;
use Nette\Database\Connection;
use Nette\Database\Conventions\DiscoveredConventions;
use Nette\Database\Explorer;
use Nette\Database\Structure;
use Nette\Database\Table\Selection;
use PDO;
use Tester\Assert;

require __DIR__ . '/BaseDataSourceTest.phpt';

final class NetteDatabaseTableDataSourceTest extends BaseDataSourceTest
{

	private Explorer $db;

	public function setUp(): void
	{
		$this->setUpDatabase();
		$this->ds = new NetteDatabaseTableDataSource($this->db->table('users'), 'id');

		$factory = new TestingDatagridFactory();
		$this->grid = $factory->createTestingDatagrid();
	}

	public function testDateRangeComparisonMatchesLegacyResults(): void
	{
		$date = new FilterDate($this->grid, 'created', 'Created', 'created');
		$date->setValue('2024-03-31');
		Assert::same([2, 3, 4], $this->dateIds($date, false));
		Assert::same([2, 3, 4], $this->dateIds($date, true));

		$date = new FilterDate($this->grid, 'only_date', 'Date', 'only_date');
		$date->setValue('2024-03-31');
		Assert::same([2, 3, 4], $this->dateIds($date, true));
	}

	public function testDateRangesKeepInclusiveAndOpenBounds(): void
	{
		foreach ([
			[['from' => '2024-03-31', 'to' => '2024-03-31'], [2, 3, 4]],
			[['from' => '2024-03-31', 'to' => ''], [2, 3, 4, 5]],
			[['from' => '', 'to' => '2024-03-31'], [1, 2, 3, 4]],
			[['from' => '', 'to' => ''], [1, 2, 3, 4, 5, 6]],
		] as [$bounds, $expected]) {
			$filter = new FilterDateRange($this->grid, 'created', 'Created', 'created', 'To');
			$filter->setValue($bounds);
			Assert::same($expected, $this->dateIds($filter, false));
			Assert::same($expected, $this->dateIds($filter, true));
		}
	}

	public function testCalendarBoundariesAndCallerDates(): void
	{
		$this->insertDate(7, '2024-02-29 23:59:59.999999');
		$this->insertDate(8, '2024-03-01 00:00:00');
		$this->insertDate(9, '2024-12-31 23:59:59.999999');
		$this->insertDate(10, '2025-01-01 00:00:00');
		foreach (['2024-02-29' => [7], '2024-12-31' => [9]] as $value => $expected) {
			$date = new FilterDate($this->grid, 'created', 'Created', 'created');
			$date->setValue($value);
			Assert::same($expected, $this->dateIds($date, true));
		}

		$from = new DateTime('2024-03-31 14:15:16', new DateTimeZone('Europe/Prague'));
		$to = new DateTime('2024-03-31 20:21:22', new DateTimeZone('Europe/Prague'));
		$filter = new FilterDateRange($this->grid, 'created', 'Created', 'created', 'To');
		$filter->setValue(['from' => $from, 'to' => $to]);
		Assert::same([2, 3, 4], $this->dateIds($filter, true));
		Assert::same('2024-03-31 14:15:16 Europe/Prague', $from->format('Y-m-d H:i:s e'));
		Assert::same('2024-03-31 20:21:22 Europe/Prague', $to->format('Y-m-d H:i:s e'));
	}

	public function testDefaultComparisonKeepsOffsetTextSemantics(): void
	{
		$this->insertDate(7, '2024-03-31T00:30:00+02:00');
		$date = new FilterDate($this->grid, 'created', 'Created', 'created');
		$date->setValue('2024-03-31');
		$source = new NetteDatabaseTableDataSource($this->db->table('date_values')->select('id'), 'id');
		$source->filter([$date]);
		Assert::same([2, 3, 4], array_column($source->getData(), 'id'));

		Assert::same([2, 3, 4], $this->dateIds($date, false));
	}

	public function testDateComparisonCanUseAnOrdinaryIndex(): void
	{
		$date = new FilterDate($this->grid, 'created', 'Created', 'created');
		$date->setValue('2024-03-31');
		$source = new NetteDatabaseTableDataSource($this->db->table('date_values')->select('id'), 'id');
		$source->setUseDateRangeComparison();
		$source->filter([$date]);
		$builder = $source->getDataSource()->getSqlBuilder();
		$statement = $this->db->getConnection()->getPdo()->prepare('EXPLAIN QUERY PLAN ' . $builder->buildSelectQuery());
		$statement->execute($builder->getParameters());
		$plan = implode(' ', array_column($statement->fetchAll(PDO::FETCH_ASSOC), 'detail'));
		Assert::contains('idx_date_values_created', $plan);
		Assert::contains('SEARCH', $plan);
	}

	protected function setUpDatabase(): void
	{
		$connection = new Connection('sqlite::memory:');
		$storage = new DevNullStorage();
		$structure = new Structure($connection, $storage);
		$conventions = new DiscoveredConventions($structure);
		$this->db = new Explorer($connection, $structure, $conventions, $storage);

		$this->db->query('CREATE TABLE users (
								id      INTEGER      PRIMARY KEY AUTOINCREMENT,
								name    VARCHAR (50),
								age     INTEGER (3),
								address VARCHAR (50)
							);
		');

		$this->db->query('CREATE TABLE date_values (id INTEGER PRIMARY KEY, created DATETIME, only_date DATE)');
		$this->db->query('CREATE INDEX idx_date_values_created ON date_values(created)');
		foreach (['2024-03-30 23:59:59', '2024-03-31 00:00:00', '2024-03-31 12:30:00', '2024-03-31 23:59:59.999999', '2024-04-01 00:00:00', null] as $key => $created) {
			$this->insertDate($key + 1, $created);
		}

		foreach ($this->data as $row) {
			$this->db->query('INSERT INTO users', $row);
		}
	}

	protected function getActualResultAsArray(): array
	{
		/** @var Selection $data */
		$data = $this->ds->getData();
		$rows = [];

		foreach ($data as $dataRow) {
			$row = [];

			foreach ($dataRow as $key => $value) {
				$row[$key] = is_numeric($value)
					? intval($value)
					: $value;
			}

			$rows[] = $row;
		}

		return $rows;
	}

	private function dateIds(FilterDate|FilterDateRange $filter, bool $optimized): array
	{
		$source = new NetteDatabaseTableDataSource($this->db->table('date_values')->select('id'), 'id');
		$source->setUseDateRangeComparison($optimized);
		$source->filter([$filter]);

		$ids = array_column($source->getData(), 'id');
		sort($ids);

		return $ids;
	}

	private function insertDate(int $id, ?string $created): void
	{
		$this->db->query('INSERT INTO date_values', [
			'id' => $id,
			'created' => $created,
			'only_date' => $created === null ? null : substr($created, 0, 10),
		]);
	}

}


$test_case = new NetteDatabaseTableDataSourceTest();
$test_case->run();
