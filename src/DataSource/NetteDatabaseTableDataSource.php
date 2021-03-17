<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\DataSource;

use LogicException;
use Nette\Database\Table\Selection;
use Nette\Utils\Strings;
use Ublaboo\DataGrid\AggregationFunction\IAggregationFunction;
use Ublaboo\DataGrid\Exception\DataGridDateTimeHelperException;
use Ublaboo\DataGrid\Filter\FilterDate;
use Ublaboo\DataGrid\Filter\FilterDateRange;
use Ublaboo\DataGrid\Filter\FilterMultiSelect;
use Ublaboo\DataGrid\Filter\FilterRange;
use Ublaboo\DataGrid\Filter\FilterSelect;
use Ublaboo\DataGrid\Filter\FilterText;
use Ublaboo\DataGrid\Utils\DateTimeHelper;
use Ublaboo\DataGrid\Utils\Sorting;

class NetteDatabaseTableDataSource extends FilterableDataSource implements IDataSource
{

	/**
	 * @var Selection
	 */
	protected $dataSource;

	/**
	 * @var array
	 */
	protected $data = [];

	/**
	 * @var string
	 */
	protected $primaryKey;


	public function __construct(Selection $dataSource, string $primaryKey)
	{
		$this->dataSource = $dataSource;
		$this->primaryKey = $primaryKey;
	}


	// *******************************************************************************
	// *                          IDataSource implementation                         *
	// *******************************************************************************


	public function getCount(): int
	{
		$dataSourceSqlBuilder = $this->dataSource->getSqlBuilder();

		try {
			$primary = $this->dataSource->getPrimary();

		} catch (LogicException $e) {
			if ($dataSourceSqlBuilder->getGroup() !== '') {
				return $this->dataSource->count(
					'DISTINCT ' . Strings::replace($dataSourceSqlBuilder->getGroup(), '~ (DESC|ASC)~')
				);
			}

			return $this->dataSource->count('*');
		}

		if ($dataSourceSqlBuilder->getGroup() !== '') {
			return $this->dataSource->count(
				'DISTINCT ' . Strings::replace($dataSourceSqlBuilder->getGroup(), '~ (DESC|ASC)~')
			);
		}

		return $this->dataSource->count(
			$this->dataSource->getName() . '.' . (is_array($primary) ? reset($primary) : $primary)
		);
	}


	/**
	 * {@inheritDoc}
	 */
	public function getData(): array
	{
		return $this->data !== []
			? $this->data
			: $this->dataSource->fetchAll();
	}


	/**
	 * {@inheritDoc}
	 */
	public function filterOne(array $condition): IDataSource
	{
		$this->dataSource->where($condition)->limit(1);

		return $this;
	}


	/**
	 * @phpstan-param positive-int|0 $offset
	 * @phpstan-param positive-int|0 $limit
	 */
	public function limit(int $offset, int $limit): IDataSource
	{
		$this->data = $this->dataSource->limit($limit, $offset)->fetchAll();

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
			$this->dataSource->getSqlBuilder()->setOrder([], []);

			foreach ($sort as $column => $order) {
				$this->dataSource->order("$column $order");
			}
		} else {
			/**
			 * Has the statement already a order by clause?
			 */
			if ($this->dataSource->getSqlBuilder()->getOrder() === []) {
				$this->dataSource->order($this->primaryKey);
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

			$this->dataSource->where("DATE({$filter->getColumn()}) = ?", $date->format('Y-m-d'));
		} catch (DataGridDateTimeHelperException $ex) {
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

				$this->dataSource->where(
					"DATE({$filter->getColumn()}) >= ?",
					$dateFrom->format('Y-m-d')
				);
			} catch (DataGridDateTimeHelperException $ex) {
				// ignore the invalid filter value
			}
		}

		if ($valueTo) {
			try {
				$dateTo = DateTimeHelper::tryConvertToDateTime($valueTo, [$filter->getPhpFormat()]);
				$dateTo->setTime(23, 59, 59);

				$this->dataSource->where("DATE({$filter->getColumn()}) <= ?", $dateTo->format('Y-m-d'));
			} catch (DataGridDateTimeHelperException $ex) {
				// ignore the invalid filter value
			}
		}
	}


	protected function applyFilterRange(FilterRange $filter): void
	{
		$conditions = $filter->getCondition();

		$valueFrom = $conditions[$filter->getColumn()]['from'];
		$valueTo = $conditions[$filter->getColumn()]['to'];

		if ($valueFrom) {
			$this->dataSource->where("{$filter->getColumn()} >= ?", $valueFrom);
		}

		if ($valueTo) {
			$this->dataSource->where("{$filter->getColumn()} <= ?", $valueTo);
		}
	}


	protected function applyFilterText(FilterText $filter): void
	{
		$or = [];
		$args = [];
		$bigOr = '(';
		$bigOrArgs = [];
		$condition = $filter->getCondition();

		foreach ($condition as $column => $value) {
			$like = '(';
			$args = [];

			if ($filter->isExactSearch()) {
				$like .= "$column = ? OR ";
				$args[] = "$value";
			} else {
				$words = $filter->hasSplitWordsSearch() === false ? [$value] : explode(' ', $value);

				foreach ($words as $word) {
					$like .= "$column LIKE ? OR ";
					$args[] = "%$word%";
				}
			}

			$like = substr($like, 0, strlen($like) - 4) . ')';

			$or[] = $like;
			$bigOr .= "$like OR ";
			$bigOrArgs = array_merge($bigOrArgs, $args);
		}

		if (sizeof($or) > 1) {
			$bigOr = substr($bigOr, 0, strlen($bigOr) - 4) . ')';

			$query = array_merge([$bigOr], $bigOrArgs);

			call_user_func_array([$this->dataSource, 'where'], $query);
		} else {
			$query = array_merge($or, $args);

			call_user_func_array([$this->dataSource, 'where'], $query);
		}
	}


	protected function applyFilterMultiSelect(FilterMultiSelect $filter): void
	{
		$condition = $filter->getCondition();
		$values = $condition[$filter->getColumn()];
		$or = '(';

		if (sizeof($values) > 1) {
			$length = sizeof($values);
			$i = 1;

			for ($iterator = 0; $iterator < count($values); $iterator++) {
				if ($i === $length) {
					$or .= $filter->getColumn() . ' = ?)';
				} else {
					$or .= $filter->getColumn() . ' = ? OR ';
				}

				$i++;
			}

			array_unshift($values, $or);

			call_user_func_array([$this->dataSource, 'where'], $values);
		} else {
			$this->dataSource->where($filter->getColumn() . ' = ?', reset($values));
		}
	}


	protected function applyFilterSelect(FilterSelect $filter): void
	{
		$this->dataSource->where($filter->getCondition());
	}


	/**
	 * {@inheritDoc}
	 */
	protected function getDataSource()
	{
		return $this->dataSource;
	}
}
