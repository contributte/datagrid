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
        ['id' => 1, 'name' => 'John Doe', 'age' => 30, 'address' => 'Blue Village 1'],
        ['id' => 2, 'name' => 'Frank Frank', 'age' => 60, 'address' => 'Yellow Garded 126'],
        ['id' => 3, 'name' => 'Santa Claus', 'age' => 12, 'address' => 'New York'],
        ['id' => 8, 'name' => 'Jude Law', 'age' => 8, 'address' => 'Lubababa 5'],
        ['id' => 30, 'name' => 'Jackie Blue', 'age' => 80, 'address' => 'Prague 678'],
        ['id' => 40, 'name' => 'John Red', 'age' => 40, 'address' => 'Porto 53'],
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

    public function testGetFilteredCount()
    {
        $filter = new FilterText($this->grid, 'a', 'b', ['name']);
        $filter->setValue('John Red');

        $this->ds->filter([$filter]);
        Assert::same(2, $this->ds->getCount());
    }

    public function testGetData()
    {
        Assert::equal($this->data, $this->getActualResultAsArray());
    }


    public function testFilterSingleColumn()
    {
        $filter = new FilterText($this->grid, 'a', 'b', ['name']);
        $filter->setValue('John Red');

        $this->ds->filter([$filter]);
        Assert::equal([
            $this->data[0],
            $this->data[5]
        ], $this->getActualResultAsArray());
    }

    public function testFilterMultipleColumns()
    {
        $filter = new FilterText($this->grid, 'a', 'b', ['name', 'address']);
        $filter->setValue('lu');
        $this->ds->filter([$filter]);


        Assert::equal([
            $this->data[0],
            $this->data[3],
            $this->data[4]
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

        Assert::equal([$this->data[5]], $this->getActualResultAsArray());
    }

    public function testFilterRangeMin()
    {

        $filter = new Ublaboo\DataGrid\Filter\FilterRange($this->grid, 'a', 'b', 'age', '-');
        $filter->setValue(['from' =>40]);
        $this->ds->filter([$filter]);

        Assert::equal([
            $this->data[1],
            $this->data[4],
            $this->data[5]
        ], $this->getActualResultAsArray());
    }

    public function testFilterRangeMax()
    {

        $filter = new Ublaboo\DataGrid\Filter\FilterRange($this->grid, 'a', 'b', 'age', '-');
        $filter->setValue(['to' => 30]);
        $this->ds->filter([$filter]);

        Assert::equal([
            $this->data[0],
            $this->data[2],
            $this->data[3]
        ], $this->getActualResultAsArray());
    }

    public function testFilterRangeMinMax()
    {

        $filter = new Ublaboo\DataGrid\Filter\FilterRange($this->grid, 'a', 'b', 'age', '-');
        $filter->setValue(['from' => 12, 'to' => 30]);
        $this->ds->filter([$filter]);

        Assert::equal([
            $this->data[0],
            $this->data[2]
        ], $this->getActualResultAsArray());
    }

    public function testFilterOne()
    {
        $this->ds->filterOne(['id' => 8]);

        Assert::equal([$this->data[3]], $this->getActualResultAsArray());
    }

    public function testFilterExactSearch(){

        $filter = new FilterText($this->grid, 'a', 'b', ['name']);
        $filter->setExactSearch();
        $filter->setValue('John Red');

        $this->ds->filter([$filter]);

        Assert::equal([$this->data[5]], $this->getActualResultAsArray());
    }

    public function testFilterExactSearchId(){

        $filter = new FilterText($this->grid, 'a', 'b', ['id']);
        $filter->setExactSearch();
        $filter->setValue('3');

        $this->ds->filter([$filter]);

        Assert::equal([$this->data[2]], $this->getActualResultAsArray());
    }

    public function testLimit()
    {
        $this->ds->limit(2, 2);
        $result = $this->getActualResultAsArray();
        Assert::equal([
            $this->data[2],
            $this->data[3]
        ], $result);
    }


    public function testSort()
    {
        $this->ds->sort(new Sorting(['name' => 'DESC']));

        $result = $this->getActualResultAsArray();

        Assert::equal([
            $this->data[2],
            $this->data[3],
            $this->data[5],
            $this->data[0],
            $this->data[4],
            $this->data[1]
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
