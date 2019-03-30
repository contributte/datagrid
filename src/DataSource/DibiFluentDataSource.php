<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\DataSource;

use Dibi;
use DibiFluent;
use Dibi\Fluent;
use Ublaboo\DataGrid\AggregationFunction\IAggregatable;
use Ublaboo\DataGrid\Filter;
use Ublaboo\DataGrid\Filter\FilterDate;
use Ublaboo\DataGrid\Filter\FilterDateRange;
use Ublaboo\DataGrid\Filter\FilterMultiSelect;
use Ublaboo\DataGrid\Filter\FilterRange;
use Ublaboo\DataGrid\Filter\FilterSelect;
use Ublaboo\DataGrid\Filter\FilterText;
use Ublaboo\DataGrid\Utils\DateTimeHelper;
use Ublaboo\DataGrid\Utils\Sorting;

class DibiFluentDataSource extends FilterableDataSource implements IDataSource, IAggregatable
{

	/**
	 * @var Fluent
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


	public function __construct(Fluent $dataSource, string $primaryKey)
	{
		$this->dataSource = $dataSource;
		$this->primaryKey = $primaryKey;
	}


	/********************************************************************************
	 *                          IDataSource implementation                          *
	 ********************************************************************************/


	/**
	 * {@inheritDoc}
	 */
	public function getCount(): int
	{
		return $this->dataSource->count();
	}


	/**
	 * {@inheritDoc}
	 */
	public function getData(): array
	{
		return $this->data ?: $this->dataSource->fetchAll();
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
	 * {@inheritDoc}
	 */
	public function limit(int $offset, int $limit): IDataSource
	{
		$this->dataSource->limit($limit)->offset($offset);

		$this->data = $this->dataSource->fetchAll();

		return $this;
	}


	/**
	 * {@inheritDoc}
	 */
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

		if (!empty($sort)) {
			$this->dataSource->removeClause('ORDER BY');
			$this->dataSource->orderBy($sort);
		} else {
			/**
			 * Has the statement already a order by clause?
			 */
			$this->dataSource->clause('ORDER BY');

			$reflection = new \ReflectionClass(Fluent::class);

			$cursorProperty = $reflection->getProperty('cursor');
			$cursorProperty->setAccessible(true);
			$cursor = $cursorProperty->getValue($this->dataSource);

			if (!$cursor) {
				$this->dataSource->orderBy($this->primaryKey);
			}
		}

		return $this;
	}


	public function processAggregation(callable $aggregationCallback): void
	{
		call_user_func($aggregationCallback, clone $this->dataSource);
	}


	/**
	 * {@inheritDoc}
	 */
	protected function applyFilterDate(FilterDate $filter): void
	{
		$conditions = $filter->getCondition();

		$date = DateTimeHelper::tryConvertToDateTime($conditions[$filter->getColumn()], [$filter->getPhpFormat()]);

		$this->dataSource->where('DATE(%n) = ?', $filter->getColumn(), $date->format('Y-m-d'));
	}


	/**
	 * {@inheritDoc}
	 */
	protected function applyFilterDateRange(FilterDateRange $filter): void
	{
		$conditions = $filter->getCondition();

		$valueFrom = $conditions[$filter->getColumn()]['from'];
		$valueTo = $conditions[$filter->getColumn()]['to'];

		if ($valueFrom) {
			$dateFrom = DateTimeHelper::tryConvertToDateTime($valueFrom, [$filter->getPhpFormat()]);
			$dateFrom->setTime(0, 0, 0);

			$this->dataSource->where('DATE(%n) >= ?', $filter->getColumn(), $dateFrom);
		}

		if ($valueTo) {
			$dateTo = DateTimeHelper::tryConvertToDateTime($valueTo, [$filter->getPhpFormat()]);
			$dateTo->setTime(23, 59, 59);

			$this->dataSource->where('DATE(%n) <= ?', $filter->getColumn(), $dateTo);
		}
	}


	/**
	 * {@inheritDoc}
	 */
	protected function applyFilterRange(FilterRange $filter): void
	{
		$conditions = $filter->getCondition();

		$valueFrom = $conditions[$filter->getColumn()]['from'];
		$valueTo = $conditions[$filter->getColumn()]['to'];

		if ($valueFrom || $valueFrom != '') {
			$this->dataSource->where('%n >= ?', $filter->getColumn(), $valueFrom);
		}

		if ($valueTo || $valueTo != '') {
			$this->dataSource->where('%n <= ?', $filter->getColumn(), $valueTo);
		}
	}


	/**
	 * {@inheritDoc}
	 */
	protected function applyFilterText(FilterText $filter): void
	{
		$condition = $filter->getCondition();
		$driver = $this->dataSource->getConnection()->getDriver();
		$or = [];

		foreach ($condition as $column => $value) {
			$column = Dibi\Helpers::escape($driver, $column, \dibi::IDENTIFIER);

			if ($filter->isExactSearch()) {
				$this->dataSource->where("$column = %s", $value);
				continue;
			}

			if ($filter->hasSplitWordsSearch() === false) {
				$words = [$value];
			} else {
				$words = explode(' ', $value);
			}

			foreach ($words as $word) {
				$or[] = ["$column LIKE %~like~", $word];
			}
		}

		if (sizeof($or) > 1) {
			$this->dataSource->where('(%or)', $or);
		} else {
			$this->dataSource->where($or);
		}
	}


	/**
	 * {@inheritDoc}
	 */
	protected function applyFilterMultiSelect(FilterMultiSelect $filter): void
	{
		$condition = $filter->getCondition();
		$values = $condition[$filter->getColumn()];
		$or = [];

		if (sizeof($values) > 1) {
			$value1 = array_shift($values);
			$length = sizeof($values);
			$i = 1;

			$this->dataSource->where('(%n = ?', $filter->getColumn(), $value1);

			foreach ($values as $value) {
				if ($i == $length) {
					$this->dataSource->or('%n = ?)', $filter->getColumn(), $value);
				} else {
					$this->dataSource->or('%n = ?', $filter->getColumn(), $value);
				}

				$i++;
			}
		} else {
			$this->dataSource->where('%n = ?', $filter->getColumn(), reset($values));
		}
	}


	/**
	 * {@inheritDoc}
	 */
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
