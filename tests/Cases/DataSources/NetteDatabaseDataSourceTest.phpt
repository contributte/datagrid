<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases\DataSources;

use Contributte\Datagrid\DataSource\NetteDatabaseDataSource;
use Contributte\Datagrid\Filter\FilterDate;
use Contributte\Datagrid\Filter\FilterDateRange;
use Contributte\Datagrid\Filter\FilterMultiSelect;
use Contributte\Datagrid\Filter\FilterRange;
use Contributte\Datagrid\Filter\FilterSelect;
use Contributte\Datagrid\Filter\FilterText;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;
use Contributte\Datagrid\Utils\Sorting;
use Nette\Caching\Storages\DevNullStorage;
use Nette\Database\Connection;
use Nette\Database\Conventions\DiscoveredConventions;
use Nette\Database\Explorer;
use Nette\Database\Structure;
use Tester\Assert;

require __DIR__ . '/BaseDataSourceTest.phpt';

final class NetteDatabaseDataSourceTest extends BaseDataSourceTest
{

	private Explorer $db;

	public function setUp(): void
	{
		$this->setUpDatabase();
		$this->ds = new NetteDatabaseDataSource($this->db, 'SELECT * FROM users');

		$factory = new TestingDatagridFactory();
		$this->grid = $factory->createTestingDatagrid();
	}

	public function testGetQueryFilterOne(): void
	{
		$s = new NetteDatabaseDataSource($this->db, 'SELECT * FROM users');
		$s->filterOne(['id' => 1]);
		[$sql, $params] = $s->getQuery();

		Assert::same('SELECT * FROM (SELECT * FROM users) AS datagrid_base WHERE id = ?', $sql);
		Assert::same([1], $params);
	}

	public function testGetQuerySort(): void
	{
		$s = new NetteDatabaseDataSource($this->db, 'SELECT * FROM users');
		$s->sort(new Sorting(['name' => 'DESC']));
		[$sql] = $s->getQuery();

		Assert::same('SELECT * FROM users ORDER BY name DESC', $sql);
	}

	public function testGetQuerySortWithFilter(): void
	{
		$s = new NetteDatabaseDataSource($this->db, 'SELECT * FROM users');
		$filter = new FilterSelect($this->grid, 'a', 'b', [1 => 'Active'], 'status');
		$filter->setValue(1);
		$s->filter([$filter]);
		$s->sort(new Sorting(['name' => 'DESC']));
		[$sql, $params] = $s->getQuery();

		Assert::same('SELECT * FROM (SELECT * FROM users) AS datagrid_base WHERE status = ? ORDER BY name DESC', $sql);
		Assert::same([1], $params);
	}

	public function testGetQueryFilterText(): void
	{
		$s = new NetteDatabaseDataSource($this->db, 'SELECT * FROM users');
		$filter = new FilterText($this->grid, 'a', 'b', ['name']);
		$filter->setValue('text');
		$s->filter([$filter]);
		[$sql, $params] = $s->getQuery();

		Assert::same('SELECT * FROM (SELECT * FROM users) AS datagrid_base WHERE (name LIKE ?)', $sql);
		Assert::same(['%text%'], $params);
	}

	public function testGetQueryFilterTextMultipleColumns(): void
	{
		$s = new NetteDatabaseDataSource($this->db, 'SELECT * FROM users');
		$filter = new FilterText($this->grid, 'a', 'b', ['name', 'address']);
		$filter->setValue('text');
		$s->filter([$filter]);
		[$sql, $params] = $s->getQuery();

		Assert::same('SELECT * FROM (SELECT * FROM users) AS datagrid_base WHERE ((name LIKE ?) OR (address LIKE ?))', $sql);
		Assert::same(['%text%', '%text%'], $params);
	}

	public function testGetQueryFilterTextSplitWords(): void
	{
		$s = new NetteDatabaseDataSource($this->db, 'SELECT * FROM users');
		$filter = new FilterText($this->grid, 'a', 'b', ['name', 'address']);
		$filter->setValue('foo bar');
		$s->filter([$filter]);
		[$sql, $params] = $s->getQuery();

		Assert::same('SELECT * FROM (SELECT * FROM users) AS datagrid_base WHERE ((name LIKE ? OR name LIKE ?) OR (address LIKE ? OR address LIKE ?))', $sql);
		Assert::same(['%foo%', '%bar%', '%foo%', '%bar%'], $params);
	}

	public function testGetQueryFilterTextSplitWordsDisabled(): void
	{
		$s = new NetteDatabaseDataSource($this->db, 'SELECT * FROM users');
		$filter = new FilterText($this->grid, 'a', 'b', ['name', 'address']);
		$filter->setValue('foo bar');
		$filter->setSplitWordsSearch(false);
		$s->filter([$filter]);
		[$sql, $params] = $s->getQuery();

		Assert::same('SELECT * FROM (SELECT * FROM users) AS datagrid_base WHERE ((name LIKE ?) OR (address LIKE ?))', $sql);
		Assert::same(['%foo bar%', '%foo bar%'], $params);
	}

	public function testGetQueryFilterTextExactSearch(): void
	{
		$s = new NetteDatabaseDataSource($this->db, 'SELECT * FROM users');
		$filter = new FilterText($this->grid, 'a', 'b', ['name', 'address']);
		$filter->setExactSearch();
		$filter->setValue('John');
		$s->filter([$filter]);
		[$sql, $params] = $s->getQuery();

		Assert::same('SELECT * FROM (SELECT * FROM users) AS datagrid_base WHERE ((name = ?) OR (address = ?))', $sql);
		Assert::same(['John', 'John'], $params);
	}

	public function testGetQueryFilterRange(): void
	{
		$s = new NetteDatabaseDataSource($this->db, 'SELECT * FROM users');
		$filter = new FilterRange($this->grid, 'a', 'b', 'age', '-');
		$filter->setValue(['from' => 10, 'to' => 50]);
		$s->filter([$filter]);
		[$sql, $params] = $s->getQuery();

		Assert::same('SELECT * FROM (SELECT * FROM users) AS datagrid_base WHERE age >= ? AND age <= ?', $sql);
		Assert::same([10, 50], $params);
	}

	public function testGetQueryFilterMultiSelect(): void
	{
		$s = new NetteDatabaseDataSource($this->db, 'SELECT * FROM users');
		$filter = new FilterMultiSelect($this->grid, 'a', 'b', [1 => 'A', 2 => 'B'], 'status');
		$filter->setValue([1, 2]);
		$s->filter([$filter]);
		[$sql, $params] = $s->getQuery();

		Assert::same('SELECT * FROM (SELECT * FROM users) AS datagrid_base WHERE status IN (?, ?)', $sql);
		Assert::same([1, 2], $params);
	}

	public function testGetQueryWithInitialParams(): void
	{
		$s = new NetteDatabaseDataSource($this->db, 'SELECT * FROM users WHERE active = ?', [1]);
		$filter = new FilterSelect($this->grid, 'a', 'b', ['John Red' => 'John Red'], 'name');
		$filter->setValue('John Red');
		$s->filter([$filter]);
		[$sql, $params] = $s->getQuery();

		Assert::same('SELECT * FROM (SELECT * FROM users WHERE active = ?) AS datagrid_base WHERE name = ?', $sql);
		Assert::same([1, 'John Red'], $params);
	}

	public function testGetDataSource(): void
	{
		$s = new NetteDatabaseDataSource($this->db, 'SELECT * FROM users');
		Assert::same($this->db, $s->getDataSource());
	}

	public function testGetDataCached(): void
	{
		$s = new NetteDatabaseDataSource($this->db, 'SELECT * FROM users');
		$s->limit(0, 3);
		// Second call returns cached $this->data, not a new query
		Assert::count(3, $s->getData());
	}

	public function testSortCallback(): void
	{
		$s = new NetteDatabaseDataSource($this->db, 'SELECT * FROM users');
		$capture = new \stdClass();
		$capture->sql = null;
		$capture->sort = null;
		$sorting = new Sorting(['name' => 'DESC'], static function (string $sql, array $sort) use ($capture): void {
			$capture->sql = $sql;
			$capture->sort = $sort;
		});
		$s->sort($sorting);

		Assert::same('SELECT * FROM users', $capture->sql);
		Assert::same(['name' => 'DESC'], $capture->sort);

		// orderByClause stays null — callback is responsible for sorting
		[$sql] = $s->getQuery();
		Assert::same('SELECT * FROM users', $sql);
	}

	public function testSortEmptyArray(): void
	{
		$s = new NetteDatabaseDataSource($this->db, 'SELECT * FROM users');
		$s->sort(new Sorting([]));
		[$sql] = $s->getQuery();

		// No ORDER BY added when sort array is empty
		Assert::same('SELECT * FROM users', $sql);
	}

	public function testFilterDate(): void
	{
		$s = new NetteDatabaseDataSource($this->db, 'SELECT * FROM users');
		$filter = new FilterDate($this->grid, 'a', 'b', 'created');
		$filter->setValue('12. 12. 2012');
		$s->filter([$filter]);
		[$sql, $params] = $s->getQuery();

		Assert::same('SELECT * FROM (SELECT * FROM users) AS datagrid_base WHERE DATE(created) = ?', $sql);
		Assert::same(['2012-12-12'], $params);
	}

	public function testFilterDateInvalid(): void
	{
		$s = new NetteDatabaseDataSource($this->db, 'SELECT * FROM users');
		$filter = new FilterDate($this->grid, 'a', 'b', 'created');
		$filter->setValue('not-a-date');
		$s->filter([$filter]);
		[$sql, $params] = $s->getQuery();

		// Invalid date is silently ignored — no condition added
		Assert::same('SELECT * FROM users', $sql);
		Assert::same([], $params);
	}

	public function testFilterDateRange(): void
	{
		$s = new NetteDatabaseDataSource($this->db, 'SELECT * FROM users');
		$filter = new FilterDateRange($this->grid, 'a', 'b', 'created', '-');
		$filter->setValue(['from' => '1. 2. 2003', 'to' => '3. 12. 2149']);
		$s->filter([$filter]);
		[$sql, $params] = $s->getQuery();

		Assert::same('SELECT * FROM (SELECT * FROM users) AS datagrid_base WHERE DATE(created) >= ? AND DATE(created) <= ?', $sql);
		Assert::same(['2003-02-01', '2149-12-03'], $params);
	}

	public function testFilterDateRangeOnlyFrom(): void
	{
		$s = new NetteDatabaseDataSource($this->db, 'SELECT * FROM users');
		$filter = new FilterDateRange($this->grid, 'a', 'b', 'created', '-');
		$filter->setValue(['from' => '1. 2. 2003', 'to' => '']);
		$s->filter([$filter]);
		[$sql, $params] = $s->getQuery();

		Assert::same('SELECT * FROM (SELECT * FROM users) AS datagrid_base WHERE DATE(created) >= ?', $sql);
		Assert::same(['2003-02-01'], $params);
	}

	public function testFilterDateRangeOnlyTo(): void
	{
		$s = new NetteDatabaseDataSource($this->db, 'SELECT * FROM users');
		$filter = new FilterDateRange($this->grid, 'a', 'b', 'created', '-');
		$filter->setValue(['from' => '', 'to' => '3. 12. 2149']);
		$s->filter([$filter]);
		[$sql, $params] = $s->getQuery();

		Assert::same('SELECT * FROM (SELECT * FROM users) AS datagrid_base WHERE DATE(created) <= ?', $sql);
		Assert::same(['2149-12-03'], $params);
	}

	public function testFilterDateRangeInvalidFrom(): void
	{
		$s = new NetteDatabaseDataSource($this->db, 'SELECT * FROM users');
		$filter = new FilterDateRange($this->grid, 'a', 'b', 'created', '-');
		$filter->setValue(['from' => 'invalid', 'to' => '3. 12. 2149']);
		$s->filter([$filter]);
		[$sql, $params] = $s->getQuery();

		// Invalid from is silently ignored — only to condition added
		Assert::same('SELECT * FROM (SELECT * FROM users) AS datagrid_base WHERE DATE(created) <= ?', $sql);
		Assert::same(['2149-12-03'], $params);
	}

	public function testFilterDateRangeInvalidTo(): void
	{
		$s = new NetteDatabaseDataSource($this->db, 'SELECT * FROM users');
		$filter = new FilterDateRange($this->grid, 'a', 'b', 'created', '-');
		$filter->setValue(['from' => '1. 2. 2003', 'to' => 'invalid']);
		$s->filter([$filter]);
		[$sql, $params] = $s->getQuery();

		// Invalid to is silently ignored — only from condition added
		Assert::same('SELECT * FROM (SELECT * FROM users) AS datagrid_base WHERE DATE(created) >= ?', $sql);
		Assert::same(['2003-02-01'], $params);
	}

	public function testFilterTextConjunctionMultipleColumns(): void
	{
		$s = new NetteDatabaseDataSource($this->db, 'SELECT * FROM users');
		$filter = new FilterText($this->grid, 'a', 'b', ['name', 'address']);
		$filter->setConjunctionSearch();
		$filter->setValue('John');
		$s->filter([$filter]);
		[$sql, $params] = $s->getQuery();

		Assert::same('SELECT * FROM (SELECT * FROM users) AS datagrid_base WHERE ((name LIKE ?) AND (address LIKE ?))', $sql);
		Assert::same(['%John%', '%John%'], $params);
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
		)');

		foreach ($this->data as $row) {
			$this->db->query('INSERT INTO users', $row);
		}
	}

	protected function getActualResultAsArray(): array
	{
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

$test_case = new NetteDatabaseDataSourceTest();
$test_case->run();
