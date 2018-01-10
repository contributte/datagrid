<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\DataSource;

use Nette\Database\Table\Selection;
use Nette\Utils\Strings;
use Ublaboo\DataGrid\Filter;
use Ublaboo\DataGrid\Utils\DateTimeHelper;
use Ublaboo\DataGrid\Utils\Sorting;

class NetteDatabaseTableDataSource extends FilterableDataSource implements IDataSource
{

	/**
	 * @var Selection
	 */
	protected $data_source;

	/**
	 * @var array
	 */
	protected $data = [];

	/**
	 * @var string
	 */
	protected $primary_key;


	/**
	 * @param Selection $data_source
	 * @param string $primary_key
	 */
	public function __construct(Selection $data_source, $primary_key)
	{
		$this->data_source = $data_source;
		$this->primary_key = $primary_key;
	}


	/********************************************************************************
	 *                          IDataSource implementation                          *
	 ********************************************************************************/


	/**
	 * Get count of data
	 * @return int
	 */
	public function getCount()
	{
		$data_source_sql_builder = $this->data_source->getSqlBuilder();

		try {
			$primary = $this->data_source->getPrimary();

		} catch (\LogicException $e) {
			if ($data_source_sql_builder->getGroup() !== '') {
				return $this->data_source->count(
					'DISTINCT ' . Strings::replace($data_source_sql_builder->getGroup(), '~ (DESC|ASC)~')
				);
			}

			return $this->data_source->count('*');
		}

		if ($data_source_sql_builder->getGroup() !== '') {
			return $this->data_source->count(
				'DISTINCT ' . Strings::replace($data_source_sql_builder->getGroup(), '~ (DESC|ASC)~')
			);
		} else {
			return $this->data_source->count(
				$this->data_source->getName() . '.' . (is_array($primary) ? reset($primary) : $primary)
			);
		}
	}


	/**
	 * Get the data
	 * @return array
	 */
	public function getData()
	{
		return $this->data ?: $this->data_source->fetchAll();
	}


	/**
	 * Filter data - get one row
	 * @param array $condition
	 * @return static
	 */
	public function filterOne(array $condition)
	{
		$this->data_source->where($condition)->limit(1);

		return $this;
	}


	/**
	 * Filter by date
	 * @param  Filter\FilterDate $filter
	 * @return void
	 */
	public function applyFilterDate(Filter\FilterDate $filter)
	{
		$conditions = $filter->getCondition();

		$date = DateTimeHelper::tryConvertToDateTime($conditions[$filter->getColumn()], [$filter->getPhpFormat()]);

		$this->data_source->where("DATE({$filter->getColumn()}) = ?", $date->format('Y-m-d'));
	}


	/**
	 * Filter by date range
	 * @param  Filter\FilterDateRange $filter
	 * @return void
	 */
	public function applyFilterDateRange(Filter\FilterDateRange $filter)
	{
		$conditions = $filter->getCondition();

		$value_from = $conditions[$filter->getColumn()]['from'];
		$value_to = $conditions[$filter->getColumn()]['to'];

		if ($value_from) {
			$date_from = DateTimeHelper::tryConvertToDateTime($value_from, [$filter->getPhpFormat()]);
			$date_from->setTime(0, 0, 0);

			$this->data_source->where("DATE({$filter->getColumn()}) >= ?", $date_from->format('Y-m-d'));
		}

		if ($value_to) {
			$date_to = DateTimeHelper::tryConvertToDateTime($value_to, [$filter->getPhpFormat()]);
			$date_to->setTime(23, 59, 59);

			$this->data_source->where("DATE({$filter->getColumn()}) <= ?", $date_to->format('Y-m-d'));
		}
	}


	/**
	 * Filter by range
	 * @param  Filter\FilterRange $filter
	 * @return void
	 */
	public function applyFilterRange(Filter\FilterRange $filter)
	{
		$conditions = $filter->getCondition();

		$value_from = $conditions[$filter->getColumn()]['from'];
		$value_to = $conditions[$filter->getColumn()]['to'];

		if ($value_from) {
			$this->data_source->where("{$filter->getColumn()} >= ?", $value_from);
		}

		if ($value_to) {
			$this->data_source->where("{$filter->getColumn()} <= ?", $value_to);
		}
	}


	/**
	 * Filter by keyword
	 * @param  Filter\FilterText $filter
	 * @return void
	 */
	public function applyFilterText(Filter\FilterText $filter)
	{
		$or = [];
		$args = [];
		$big_or = '(';
		$big_or_args = [];
		$condition = $filter->getCondition();

		foreach ($condition as $column => $value) {
			$like = '(';
			$args = [];

			if ($filter->isExactSearch()) {
				$like .= "$column = ? OR ";
				$args[] = "$value";
			} else {
				if ($filter->hasSplitWordsSearch() === false) {
					$words = [$value];
				} else {
					$words = explode(' ', $value);
				}
				foreach ($words as $word) {
					$like .= "$column LIKE ? OR ";
					$args[] = "%$word%";
				}
			}
			$like = substr($like, 0, strlen($like) - 4) . ')';

			$or[] = $like;
			$big_or .= "$like OR ";
			$big_or_args = array_merge($big_or_args, $args);
		}

		if (sizeof($or) > 1) {
			$big_or = substr($big_or, 0, strlen($big_or) - 4) . ')';

			$query = array_merge([$big_or], $big_or_args);

			call_user_func_array([$this->data_source, 'where'], $query);
		} else {
			$query = array_merge($or, $args);

			call_user_func_array([$this->data_source, 'where'], $query);
		}
	}


	/**
	 * Filter by multi select value
	 * @param  Filter\FilterMultiSelect $filter
	 * @return void
	 */
	public function applyFilterMultiSelect(Filter\FilterMultiSelect $filter)
	{
		$condition = $filter->getCondition();
		$values = $condition[$filter->getColumn()];
		$or = '(';

		if (sizeof($values) > 1) {
			$length = sizeof($values);
			$i = 1;

			foreach ($values as $value) {
				if ($i == $length) {
					$or .= $filter->getColumn() . ' = ?)';
				} else {
					$or .= $filter->getColumn() . ' = ? OR ';
				}

				$i++;
			}

			array_unshift($values, $or);

			call_user_func_array([$this->data_source, 'where'], $values);
		} else {
			$this->data_source->where($filter->getColumn() . ' = ?', reset($values));
		}
	}


	/**
	 * Filter by select value
	 * @param  Filter\FilterSelect $filter
	 * @return void
	 */
	public function applyFilterSelect(Filter\FilterSelect $filter)
	{
		$this->data_source->where($filter->getCondition());
	}


	/**
	 * Apply limit and offset on data
	 * @param int $offset
	 * @param int $limit
	 * @return static
	 */
	public function limit($offset, $limit)
	{
		$this->data = $this->data_source->limit($limit, $offset)->fetchAll();

		return $this;
	}


	/**
	 * Sort data
	 * @param  Sorting $sorting
	 * @return static
	 */
	public function sort(Sorting $sorting)
	{
		if (is_callable($sorting->getSortCallback())) {
			call_user_func(
				$sorting->getSortCallback(),
				$this->data_source,
				$sorting->getSort()
			);

			return $this;
		}

		$sort = $sorting->getSort();

		if (!empty($sort)) {
			$this->data_source->getSqlBuilder()->setOrder([], []);

			foreach ($sort as $column => $order) {
				$this->data_source->order("$column $order");
			}
		} else {
			/**
			 * Has the statement already a order by clause?
			 */
			if (!$this->data_source->getSqlBuilder()->getOrder()) {
				$this->data_source->order($this->primary_key);
			}
		}

		return $this;
	}


	/**
	 * @param  callable $aggregationCallback
	 * @return void
	 */
	public function processAggregation(callable $aggregationCallback)
	{
		call_user_func($aggregationCallback, $this->data_source);
	}
}
