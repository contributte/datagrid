<?php declare(strict_types = 1);

namespace Contributte\Datagrid\DataSource;

use Contributte\Datagrid\AggregationFunction\IAggregatable;
use Contributte\Datagrid\AggregationFunction\IAggregationFunction;
use Contributte\Datagrid\Exception\DatagridDateTimeHelperException;
use Contributte\Datagrid\Filter\FilterDate;
use Contributte\Datagrid\Filter\FilterDateRange;
use Contributte\Datagrid\Filter\FilterMultiSelect;
use Contributte\Datagrid\Filter\FilterRange;
use Contributte\Datagrid\Filter\FilterSelect;
use Contributte\Datagrid\Filter\FilterText;
use Contributte\Datagrid\Utils\DateTimeHelper;
use Contributte\Datagrid\Utils\Sorting;
use Dibi\Fluent;
use Dibi\Helpers;
use ReflectionClass;

class DibiFluentDataSource extends FilterableDataSource implements IDataSource, IAggregatable
{

	protected array $data = [];

	public function __construct(protected Fluent $dataSource, protected string $primaryKey)
	{
	}

	public function getCount(): int
	{
		return $this->dataSource->count();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getData(): array
	{
		return $this->data !== [] ? $this->data : $this->dataSource->fetchAll();
	}

	/**
	 * {@inheritDoc}
	 */
	public function filterOne(array $condition): IDataSource
	{
		$this->dataSource->where($condition)->limit(1);

		return $this;
	}

	public function limit(int $offset, int $limit): IDataSource
	{
		$this->dataSource->limit($limit)->offset($offset);

		$this->data = $this->dataSource->fetchAll();

		return $this;
	}

	public function sort(Sorting $sorting): IDataSource
	{
		if (is_callable($sorting->getSortCallback())) {
			call_user_func(
				$sorting->getSortCallback(),
				$this->dataSource,
				$sorting->getSort()
			);

			return $this;
		}

		$sort = $sorting->getSort();

		if ($sort !== []) {
			$this->dataSource->removeClause('ORDER BY');
			$this->dataSource->orderBy($sort);
		} else {
			/**
			 * Has the statement already a order by clause?
			 */
			$this->dataSource->clause('ORDER BY');

			$reflection = new ReflectionClass(Fluent::class);

			$cursorProperty = $reflection->getProperty('cursor');
			$cursorProperty->setAccessible(true);
			$cursor = $cursorProperty->getValue($this->dataSource);

			if (!(bool) $cursor) {
				$this->dataSource->orderBy($this->primaryKey);
			}
		}

		return $this;
	}

	public function processAggregation(IAggregationFunction $function): void
	{
		$function->processDataSource(clone $this->dataSource);
	}

	protected function applyFilterDate(FilterDate $filter): void
	{
		$conditions = $filter->getCondition();

		try {
			$date = DateTimeHelper::tryConvertToDateTime($conditions[$filter->getColumn()], [$filter->getPhpFormat()]);

			$this->dataSource->where('DATE(%n) = ?', $filter->getColumn(), $date->format('Y-m-d'));
		} catch (DatagridDateTimeHelperException) {
			// ignore the invalid filter value
		}
	}

	protected function applyFilterDateRange(FilterDateRange $filter): void
	{
		$conditions = $filter->getCondition();

		$valueFrom = $conditions[$filter->getColumn()]['from'];
		$valueTo = $conditions[$filter->getColumn()]['to'];

		if ($valueFrom) {
			try {
				$dateFrom = DateTimeHelper::tryConvertToDateTime($valueFrom, [$filter->getPhpFormat()]);
				$dateFrom->setTime(0, 0, 0);

				$this->dataSource->where('DATE(%n) >= ?', $filter->getColumn(), $dateFrom);
			} catch (DatagridDateTimeHelperException) {
				// ignore the invalid filter value
			}
		}

		if ($valueTo) {
			try {
				$dateTo = DateTimeHelper::tryConvertToDateTime($valueTo, [$filter->getPhpFormat()]);
				$dateTo->setTime(23, 59, 59);

				$this->dataSource->where('DATE(%n) <= ?', $filter->getColumn(), $dateTo);
			} catch (DatagridDateTimeHelperException) {
				// ignore the invalid filter value
			}
		}
	}

	protected function applyFilterRange(FilterRange $filter): void
	{
		$conditions = $filter->getCondition();

		$valueFrom = $conditions[$filter->getColumn()]['from'];
		$valueTo = $conditions[$filter->getColumn()]['to'];

		if ($valueFrom || $valueFrom !== '') {
			$this->dataSource->where('%n >= ?', $filter->getColumn(), $valueFrom);
		}

		if ($valueTo || $valueTo !== '') {
			$this->dataSource->where('%n <= ?', $filter->getColumn(), $valueTo);
		}
	}

	protected function applyFilterText(FilterText $filter): void
	{
		$condition = $filter->getCondition();
		$driver = $this->dataSource->getConnection()->getDriver();
		$or = [];

		foreach ($condition as $column => $value) {
			$column = Helpers::escape($driver, $column, Fluent::Identifier);

			if ($filter->isExactSearch()) {
				$this->dataSource->where(sprintf('%s = %%s', $column), $value);

				continue;
			}

			$words = $filter->hasSplitWordsSearch() === false ? [$value] : explode(' ', $value);

			foreach ($words as $word) {
				$or[] = [sprintf('%s LIKE %%~like~', $column), $word];
			}
		}

		if (count($or) > 1) {
			$this->dataSource->where('(%or)', $or);
		} else {
			$this->dataSource->where($or);
		}
	}

	protected function applyFilterMultiSelect(FilterMultiSelect $filter): void
	{
		$condition = $filter->getCondition();
		$values = $condition[$filter->getColumn()];

		if ((is_countable($values) ? count($values) : 0) > 1) {
			$value1 = array_shift($values);
			$length = count($values);
			$i = 1;

			$this->dataSource->where('(%n = ?', $filter->getColumn(), $value1);

			foreach ($values as $value) {
				if ($i === $length) {
					$this->dataSource->__call('or', ['%n = ?)', $filter->getColumn(), $value]);
				} else {
					$this->dataSource->__call('or', ['%n = ?', $filter->getColumn(), $value]);
				}

				$i++;
			}
		} else {
			$this->dataSource->where('%n = ?', $filter->getColumn(), reset($values));
		}
	}

	protected function applyFilterSelect(FilterSelect $filter): void
	{
		$this->dataSource->where($filter->getCondition());
	}

	protected function getDataSource(): Fluent
	{
		return $this->dataSource;
	}

}
