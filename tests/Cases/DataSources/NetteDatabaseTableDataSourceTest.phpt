<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases\DataSources;

use Contributte\Datagrid\DataSource\NetteDatabaseTableDataSource;
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

	public function testGetCountWithGroupByMultipleColumns(): void
	{
		$this->db->query('CREATE TABLE orders_products (
								id           INTEGER PRIMARY KEY AUTOINCREMENT,
								title        VARCHAR (50),
								variant      VARCHAR (50),
								course_city  VARCHAR (50),
								course_date  VARCHAR (50)
							);
		');
		$this->db->getStructure()->rebuild();

		$rows = [
			['title' => 'A', 'variant' => 'x', 'course_city' => null, 'course_date' => null],
			['title' => 'A', 'variant' => 'x', 'course_city' => null, 'course_date' => null],
			['title' => 'B', 'variant' => 'y', 'course_city' => null, 'course_date' => null],
			['title' => 'C', 'variant' => 'z', 'course_city' => 'Prague', 'course_date' => '2021-01-01'],
		];

		foreach ($rows as $row) {
			$this->db->query('INSERT INTO orders_products', $row);
		}

		$selection = $this->db->table('orders_products')
			->group('title, variant, course_city, course_date');

		$ds = new NetteDatabaseTableDataSource($selection, 'id');

		Assert::same(3, $ds->getCount());
		Assert::same(3, count($ds->getData()));
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
