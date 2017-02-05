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

        $this->db->query(file_get_contents(__DIR__ . '/config/schema_users.sql'));
        $this->db->query(file_get_contents(__DIR__ . '/config/schema_cities.sql'));

        foreach($this->data['users'] as $row) {
            $this->db->insert('users', $row)->execute();
        }

        foreach($this->data['cities'] as $row) {
            $this->db->insert('cities', $row)->execute();
        }
    }
}


$test_case = new DibiFluentDataSourceTest();
$test_case->run();
