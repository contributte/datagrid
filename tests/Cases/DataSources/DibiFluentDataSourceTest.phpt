<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases\DataSources;

use Contributte\Datagrid\DataSource\DibiFluentDataSource;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;
use dibi;
use Dibi\Connection;

require __DIR__ . '/BaseDataSourceTest.phpt';

// E_NOTICE: Trying to access array offset on value of type null
error_reporting(E_ERROR | E_PARSE);

final class DibiFluentDataSourceTest extends BaseDataSourceTest
{

	private Connection $db;

	public function setUp(): void
	{
		$this->setUpDatabase();
		$this->ds = new DibiFluentDataSource($this->db->select('*')->from('users'), 'id');
		$factory = new TestingDatagridFactory();
		$this->grid = $factory->createTestingDatagrid();
	}

	public function testPartialDateFilters(): void
	{
		$this->db->query('CREATE TABLE dated_items (id INTEGER PRIMARY KEY, created TEXT)');
		foreach ($this->getDateFilterRows() as $row) {
			$this->db->insert('dated_items', $row)->execute();
		}

		$this->assertPartialDateFilters(
			fn (): DibiFluentDataSource => new DibiFluentDataSource($this->db->select('*')->from('dated_items')->orderBy('id'), 'id')
		);
	}

	protected function setUpDatabase(): void
	{
		$this->db = dibi::connect([
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

		foreach ($this->data as $row) {
			$this->db->insert('users', $row)->execute();
		}
	}

}


$test_case = new DibiFluentDataSourceTest();
$test_case->run();
