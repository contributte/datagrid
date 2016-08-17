<?php

namespace Ublaboo\DataGrid\Tests\Cases;

use Tester\TestCase;
use Tester\Assert;
use Ublaboo;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Filter\FilterText;
use Ublaboo\DataGrid\Utils\Sorting;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/XTestingDataGridFactory.php';

final class DibiFluentDataSourceTest extends TestCase
{
    /**
     * @var \DibiConnection
     */
	private $db;

    /**
     * @var Ublaboo\DataGrid\DataSource\DibiFluentDataSource
     */
    private $ds;


    private $data = [
        ['id' => 1, 'name' => 'John Doe', 'age' => 30, 'address' => 'Blue Village 1'],
        ['id' => 2, 'name' => 'Frank Frank', 'age' => 60, 'address' => 'Yellow Garded 126'],
        ['id' => 3, 'name' => 'Santa Claus', 'age' => 12, 'address' => 'New York'],
        ['id' => 8, 'name' => 'Jude Law', 'age' => 8, 'address' => 'Lubababa 5'],
        ['id' => 30, 'name' => 'Jackie Blue', 'age' => 80, 'address' => 'Prague 678'],
        ['id' => 40, 'name' => 'John Red', 'age' => 40, 'address' => 'Porto 53'],
    ];

	/**
	 * @var Ublaboo\DataGrid\DataGrid
	 */
	private $grid;


	public function setUp()
	{
        $this->setUpDatabase();

		$this->ds = new Ublaboo\DataGrid\DataSource\DibiFluentDataSource($this->db->select('*')->from('users'),'id');
		$factory = new Ublaboo\DataGrid\Tests\Files\XTestingDataGridFactory;
		$this->grid = $factory->createXTestingDataGrid();
	}


	public function testGetCount()
	{
		Assert::same(6, $this->ds->getCount());
	}


	public function testGetData()
	{
	    $rows = [];
	    foreach($this->data as $row){
            $rows[] = new \DibiRow($row);
        }



		Assert::equal($this->ds->getData(), $rows);
	}


	public function testFilterSingleColumn()
	{
		$filter = new FilterText($this->grid, 'a', 'b', ['name']);
		$filter->setValue('John Red');

		$this->ds->filter([$filter]);

		Assert::equal([
		    new \DibiRow($this->data[0]),
            new \DibiRow($this->data[5])
        ], $this->ds->getData());
	}

	public function testFilterMultipleColumns(){
        $this->setUp();
        $filter = new FilterText($this->grid, 'a', 'b', ['name', 'address']);
        $filter->setValue('lu');

        $this->ds->filter([$filter]);
        Assert::equal([
            new \DibiRow($this->data[0]),
            new \DibiRow($this->data[3]),
            new \DibiRow($this->data[4])
        ], $this->ds->getData());
    }

	public function testFilterFalseSplitWordsSearch(){

        /**
         * Single column - SplitWordsSearch => FALSE
         */
        $filter = new FilterText($this->grid, 'a', 'b', ['name']);
        $filter->setSplitWordsSearch(FALSE);
        $filter->setValue('John Red');

        $this->ds->filter([$filter]);
        Assert::equal([
            new \DibiRow($this->data[5])
        ], $this->ds->getData());
    }


	public function testFilterOne()
	{
		$this->setUp();

		$this->ds->filterOne(['id' => 8]);

		Assert::equal([
		    new \DibiRow($this->data[3])
        ], $this->ds->getData());
	}


	public function testLimit()
	{
		$this->setUp();

		$this->ds->limit(2, 2);

		Assert::equal([
            new \DibiRow($this->data[2]),
            new \DibiRow($this->data[3])
        ], array_values($this->ds->getData()));
	}


	public function testSort()
	{
		$this->setUp();

		$this->ds->sort(new Sorting(['name' => 'DESC']));

		Assert::equal([
			new \DibiRow($this->data[2]),
            new \DibiRow($this->data[3]),
            new \DibiRow($this->data[5]),
            new \DibiRow($this->data[0]),
            new \DibiRow($this->data[4]),
            new \DibiRow($this->data[1])
		], array_values($this->ds->getData()));
	}

    protected function setUpDatabase()
    {
        if (file_exists('../Files/sample.s3db')){
            unlink('../Files/sample.s3db');
        }
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
