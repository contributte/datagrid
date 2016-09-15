<?php

namespace Ublaboo\DataGrid\Tests\Cases\DataSources;

use Tester\TestCase;
use Tester\Assert;
use Ublaboo;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Filter\FilterText;
use Ublaboo\DataGrid\Utils\Sorting;

require __DIR__ . '/../../bootstrap.php';
require __DIR__ . '/../../Files/XTestingDataGridFactory.php';

abstract class BaseDataSourceTest extends TestCase
{

    protected $data = [
        'users' => [
            ['id' => 1, 'name' => 'John Doe', 'age' => 30, 'address' => 'Blue Village 1', 'city' => 111],
            ['id' => 2, 'name' => 'Frank Frank', 'age' => 60, 'address' => 'Yellow Garded 126', 'city' => 2],
            ['id' => 3, 'name' => 'Santa Claus', 'age' => 12, 'address' => 'New York', 'city' => 1],
            ['id' => 8, 'name' => 'Jude Law', 'age' => 8, 'address' => 'Lubababa 5', 'city' => 1],
            ['id' => 30, 'name' => 'Jackie Blue', 'age' => 80, 'address' => 'Prague 678', 'city' => 1],
            ['id' => 40, 'name' => 'John Red', 'age' => 40, 'address' => 'Porto 53', 'city' => 1],
        ],
        'cities' => [
            ['id' => 1, 'name' => 'New Work', 'created' => '2016-01-07 01:02:03.04'],
            ['id' => 2, 'name' => 'Sydney', 'created' => '2016-01-01 01:02:03.04'],
            ['id' => 10, 'name' => 'Prague', 'created' => '2010-01-01 01:02:03.04'],
            ['id' => 11, 'name' => 'Berlin', 'created' => '2016-01-01 01:02:03.04'],
            ['id' => 12, 'name' => 'London', 'created' => '2016-03-01 01:02:03.04'],
            ['id' => 13, 'name' => 'Paris', 'created' => '2016-01-11 01:02:03.04'],
            ['id' => 14, 'name' => 'Parisis', 'created' => '2016-01-01 01:02:03.04'],
            ['id' => 60, 'name' => 'Tokio', 'created' => '2014-01-03 01:02:03.04'],
            ['id' => 111, 'name' => 'Porto', 'created' => '2016-01-01 01:02:03.04'],
            ['id' => 112, 'name' => 'Port', 'created' => '2013-01-01 01:02:03.04']
        ]
    ];

    /**
     * @var Ublaboo\DataGrid\DataSource\IDataSource
     */
    protected $ds;

    /**
     * @var Ublaboo\DataGrid\DataGrid
     */
    protected $grid;


    public function testGetCount()
    {
        Assert::same(6, $this->ds->getCount());
    }


    public function testGetDataType()
    {
        Assert::type('array', $this->ds->getData());
    }


    public function testGetData()
    {
        Assert::equal($this->data['users'], $this->getActualResultAsArray());
    }


    public function testFilterSingleColumn()
    {
        $filter = new FilterText($this->grid, 'a', 'b', ['name']);
        $filter->setValue('John Red');

        $this->ds->filter([$filter]);
        Assert::equal([
            $this->data['users'][0],
            $this->data['users'][5]
        ], $this->getActualResultAsArray());
    }

    public function testFilterMultipleColumns()
    {
        $filter = new FilterText($this->grid, 'a', 'b', ['name', 'address']);
        $filter->setValue('lu');
        $this->ds->filter([$filter]);


        Assert::equal([
            $this->data['users'][0],
            $this->data['users'][3],
            $this->data['users'][4]
        ], $this->getActualResultAsArray());
    }

    public function testFilterFalseSplitWordsSearch()
    {

        /**
         * Single column - SplitWordsSearch => FALSE
         */
        $filter = new FilterText($this->grid, 'a', 'b', ['name']);
        $filter->setSplitWordsSearch(FALSE);
        $filter->setValue('John Red');

        $this->ds->filter([$filter]);

        Assert::equal([$this->data['users'][5]], $this->getActualResultAsArray());
    }

    public function testFilterRangeMin()
    {

        $filter = new Ublaboo\DataGrid\Filter\FilterRange($this->grid, 'a', 'b', 'age', '-');
        $filter->setValue(['from' => 40]);
        $this->ds->filter([$filter]);

        Assert::equal([
            $this->data['users'][1],
            $this->data['users'][4],
            $this->data['users'][5]
        ], $this->getActualResultAsArray());
    }

    public function testFilterRangeMax()
    {

        $filter = new Ublaboo\DataGrid\Filter\FilterRange($this->grid, 'a', 'b', 'age', '-');
        $filter->setValue(['to' => 30]);
        $this->ds->filter([$filter]);

        Assert::equal([
            $this->data['users'][0],
            $this->data['users'][2],
            $this->data['users'][3]
        ], $this->getActualResultAsArray());
    }

    public function testFilterRangeMinMax()
    {

        $filter = new Ublaboo\DataGrid\Filter\FilterRange($this->grid, 'a', 'b', 'age', '-');
        $filter->setValue(['from' => 12, 'to' => 30]);
        $this->ds->filter([$filter]);

        Assert::equal([
            $this->data['users'][0],
            $this->data['users'][2]
        ], $this->getActualResultAsArray());
    }

    public function testFilterOne()
    {
        $this->ds->filterOne(['id' => 8]);

        Assert::equal([$this->data['users'][3]], $this->getActualResultAsArray());
    }


    public function testLimit()
    {
        $this->ds->limit(2, 2);
        $result = $this->getActualResultAsArray();
        Assert::equal([
            $this->data['users'][2],
            $this->data['users'][3]
        ], $result);
    }


    public function testSort()
    {
        $this->ds->sort(new Sorting(['name' => 'DESC']));

        $result = $this->getActualResultAsArray();

        Assert::equal([
            $this->data['users'][2],
            $this->data['users'][3],
            $this->data['users'][5],
            $this->data['users'][0],
            $this->data['users'][4],
            $this->data['users'][1]
        ], $result);
    }


    protected function getActualResultAsArray()
    {
        return array_values(
            json_decode(
                json_encode($this->ds->getData())
                , TRUE)
        );
    }
}

Assert::true(TRUE);