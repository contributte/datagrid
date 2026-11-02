<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases\DataSources;

use Contributte\Datagrid\DataSource\NetteDatabaseTableDataSource;
use Contributte\Datagrid\Filter\FilterDate;
use Contributte\Datagrid\Filter\FilterDateRange;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;
use Nette\Caching\Storages\DevNullStorage;
use Nette\Database\Connection;
use Nette\Database\Conventions\DiscoveredConventions;
use Nette\Database\Explorer;
use Nette\Database\Structure;
use Nette\Database\Table\Selection;
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

	public function testPartialDatesFilterActualRows(): void
	{
		$year = date('Y');
		foreach (['05-11', '05-12', '05-24', '05-25'] as $key => $day) {
			$this->db->query('INSERT INTO dated_items', ['id' => $key + 1, 'created' => $year . '-' . $day . ' 12:30:00']);
		}

		$date = new FilterDate($this->grid, 'created', 'Created', 'created');
		$date->setValue('12.5.');
		$source = new NetteDatabaseTableDataSource($this->db->table('dated_items'), 'id');
		$source->filter([$date]);
		Assert::same([2], array_column($source->getData(), 'id'));

		$range = new FilterDateRange($this->grid, 'created', 'Created', 'created', 'To');
		$range->setValue(['from' => '12.5.', 'to' => '24.5.']);
		$source = new NetteDatabaseTableDataSource($this->db->table('dated_items'), 'id');
		$source->filter([$range]);
		Assert::same([2, 3], array_column($source->getData(), 'id'));
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

		$this->db->query('CREATE TABLE dated_items (id INTEGER PRIMARY KEY, created TEXT)');
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

}


$test_case = new NetteDatabaseTableDataSourceTest();
$test_case->run();
