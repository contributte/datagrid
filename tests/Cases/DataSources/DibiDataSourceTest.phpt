<?php

namespace Ublaboo\DataGrid\Tests\Cases\DataSources;

use Ublaboo;

require __DIR__ . '/BaseDataSourceTest.phpt';

final class DibiFluentDataSourceTest extends BaseDataSourceTest
{
	/**
	 * @var \DibiConnection
	 */
	private $db;


	public function setUp()
	{
		$this->setUpDatabase();
		$this->ds = new Ublaboo\DataGrid\DataSource\DibiFluentDataSource($this->db->select('*')->from('users'),'id');
		$factory = new Ublaboo\DataGrid\Tests\Files\XTestingDataGridFactory;
		$this->grid = $factory->createXTestingDataGrid();
	}

	protected function setUpDatabase()
	{
			$this->db = \dibi::connect(array(
				'driver' => 'pdo',
				'dsn' => 'sqlite::memory:',
			));

		$this->db->query('CREATE TABLE users (
								id      INTEGER      PRIMARY KEY AUTOINCREMENT,
								name    VARCHAR (50),
								age     INTEGER (3),
								address VARCHAR (50) 
							);
		');
		foreach($this->data as $row){
			$this->db->insert('users', $row)->execute();
		}
	}
}


$test_case = new DibiFluentDataSourceTest();
$test_case->run();
