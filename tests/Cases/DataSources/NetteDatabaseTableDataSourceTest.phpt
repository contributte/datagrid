<?php

namespace Ublaboo\DataGrid\Tests\Cases\DataSources;

use Nette\Caching\Storages\DevNullStorage;
use Nette\Database\Context;
use Nette\Database\Conventions\DiscoveredConventions;
use Nette\Database\Structure;
use Nette\Database\Table\Selection;
use Ublaboo;
use Nette\Database\Connection;

require __DIR__ . '/BaseDataSourceTest.phpt';

final class NetteDatabaseTableDataSourceTest extends BaseDataSourceTest
{
	/**
	 * @var Context
	 */
	private $db;


	public function setUp()
	{
		$this->setUpDatabase();
		$this->ds = new Ublaboo\DataGrid\DataSource\NetteDatabaseTableDataSource($this->db->table('users'), 'id');

		$factory = new Ublaboo\DataGrid\Tests\Files\XTestingDataGridFactory;
		$this->grid = $factory->createXTestingDataGrid();
	}

	protected function setUpDatabase()
	{
		$connection = new Connection('sqlite::memory:');
		$storage = new DevNullStorage();
		$structure = new Structure($connection, $storage);
		$conventions = new DiscoveredConventions($structure);
		$this->db = new Context($connection, $structure, $conventions, $storage);

		$this->db->query('CREATE TABLE users (
								id      INTEGER      PRIMARY KEY AUTOINCREMENT,
								name    VARCHAR (50),
								age     INTEGER (3),
								address VARCHAR (50) 
							);
		');
		foreach($this->data as $row){
			$this->db->query('INSERT INTO users', $row);
		}
	}

	protected function getActualResultAsArray()
	{
		/** @var Selection $data */
		$data = $this->ds->getData();
		$rows = [];
		foreach ($data as $dataRow) {
			$row = [];
			foreach($dataRow as $key=>$value) {
				$row[$key] = is_numeric($value) ? intval($value) : $value;
			}
			$rows[] = $row;
		}
		return $rows;
	}
}


$test_case = new NetteDatabaseTableDataSourceTest();
$test_case->run();
