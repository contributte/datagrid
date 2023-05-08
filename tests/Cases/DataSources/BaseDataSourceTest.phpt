<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases\DataSources;

use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\DataSource\IDataSource;
use Contributte\Datagrid\Filter\FilterRange;
use Contributte\Datagrid\Filter\FilterSelect;
use Contributte\Datagrid\Filter\FilterText;
use Contributte\Datagrid\Utils\Sorting;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';
require __DIR__ . '/../../Files/TestingDatagridFactory.php';

abstract class BaseDataSourceTest extends TestCase
{

	protected array $data = [
		['id' => 1, 'name' => 'John Doe', 'age' => 30, 'address' => 'Blue Village 1'],
		['id' => 2, 'name' => 'Frank Frank', 'age' => 60, 'address' => 'Yellow Garded 126'],
		['id' => 3, 'name' => 'Santa Claus', 'age' => 12, 'address' => 'New York'],
		['id' => 8, 'name' => 'Jude Law', 'age' => 8, 'address' => 'Lubababa 5'],
		['id' => 30, 'name' => 'Jackie Blue', 'age' => 80, 'address' => 'Prague 678'],
		['id' => 40, 'name' => 'John Red', 'age' => 40, 'address' => 'Porto 53'],
	];

	protected IDataSource $ds;

	protected Datagrid $grid;

	public function testGetCount(): void
	{
		Assert::same(6, $this->ds->getCount());
	}

	public function testGetFilteredCount(): void
	{
		$filter = new FilterText($this->grid, 'a', 'b', ['name']);
		$filter->setValue('John Red');

		$this->ds->filter([$filter]);
		Assert::same(2, $this->ds->getCount());
	}

	public function testGetData(): void
	{
		Assert::equal($this->data, $this->getActualResultAsArray());
	}

	public function testFilterSingleColumn(): void
	{
		$filter = new FilterText($this->grid, 'a', 'b', ['name']);
		$filter->setValue('John Red');

		$this->ds->filter([$filter]);
		Assert::equal([
			$this->data[0],
			$this->data[5],
		], $this->getActualResultAsArray());
	}

	public function testFilterMultipleColumns(): void
	{
		$filter = new FilterText($this->grid, 'a', 'b', ['name', 'address']);
		$filter->setValue('lu');
		$this->ds->filter([$filter]);

		Assert::equal([
			$this->data[0],
			$this->data[3],
			$this->data[4],
		], $this->getActualResultAsArray());
	}

	public function testFilterFalseSplitWordsSearch(): void
	{
		/**
		 * Single column - SplitWordsSearch => FALSE
		 */
		$filter = new FilterText($this->grid, 'a', 'b', ['name']);
		$filter->setSplitWordsSearch(false);
		$filter->setValue('John Red');

		$this->ds->filter([$filter]);

		Assert::equal([$this->data[5]], $this->getActualResultAsArray());
	}

	public function testFilterRangeMin(): void
	{
		$filter = new FilterRange($this->grid, 'a', 'b', 'age', '-');
		$filter->setValue(['from' => 40]);
		$this->ds->filter([$filter]);

		Assert::equal([
			$this->data[1],
			$this->data[4],
			$this->data[5],
		], $this->getActualResultAsArray());
	}

	public function testFilterRangeMax(): void
	{
		$filter = new FilterRange($this->grid, 'a', 'b', 'age', '-');
		$filter->setValue(['to' => 30]);
		$this->ds->filter([$filter]);

		Assert::equal([
			$this->data[0],
			$this->data[2],
			$this->data[3],
		], $this->getActualResultAsArray());
	}

	public function testFilterRangeMinMax(): void
	{
		$filter = new FilterRange($this->grid, 'a', 'b', 'age', '-');
		$filter->setValue(['from' => 12, 'to' => 30]);
		$this->ds->filter([$filter]);

		Assert::equal([
			$this->data[0],
			$this->data[2],
		], $this->getActualResultAsArray());
	}

	public function testFilterOne(): void
	{
		$this->ds->filterOne(['id' => 8]);

		Assert::equal([$this->data[3]], $this->getActualResultAsArray());
	}

	public function testFilterExactSearch(): void
	{
		$filter = new FilterText($this->grid, 'a', 'b', ['name']);
		$filter->setExactSearch();
		$filter->setValue('John Red');

		$this->ds->filter([$filter]);

		Assert::equal([$this->data[5]], $this->getActualResultAsArray());
	}

	public function testFilterExactSearchId(): void
	{
		$filter = new FilterText($this->grid, 'a', 'b', ['id']);
		$filter->setExactSearch();
		$filter->setValue(3);

		$this->ds->filter([$filter]);

		Assert::equal([$this->data[2]], $this->getActualResultAsArray());
	}

	public function testLimit(): void
	{
		$this->ds->limit(2, 2);
		$result = $this->getActualResultAsArray();
		Assert::equal([
			$this->data[2],
			$this->data[3],
		], $result);
	}

	public function testSort(): void
	{
		$this->ds->sort(new Sorting(['name' => 'DESC']));

		$result = $this->getActualResultAsArray();

		Assert::equal([
			$this->data[2],
			$this->data[3],
			$this->data[5],
			$this->data[0],
			$this->data[4],
			$this->data[1],
		], $result);
	}

	public function testFilterSelect(): void
	{
		$filter = new FilterSelect($this->grid, 'a', 'b', ['John Red' => 'John Red'], 'name');
		$filter->setValue('John Red');

		$this->ds->filter([$filter]);

		Assert::equal([$this->data[5]], $this->getActualResultAsArray());
	}

	protected function getActualResultAsArray(): array
	{
		return array_values(
			json_decode(
				json_encode($this->ds->getData()),
				true
			)
		);
	}

}

Assert::true(true);
