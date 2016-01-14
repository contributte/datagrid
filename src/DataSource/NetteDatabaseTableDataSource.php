<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\DataSource;

use DibiFluent,
	Nette\Utils\Callback,
	Nette\Utils\Strings,
	Ublaboo\DataGrid\Filter;

class NetteDatabaseTableDataSource
{

	/**
	 * @var DibiFluent
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


	public function __construct($data_source, $primary_key)
	{
		$this->data_source = $data_source;
		$this->primary_key = $primary_key;
	}


	/********************************************************************************
	 *                          IDataSource implementation                          *
	 ********************************************************************************/


	/**
	 * @return int
	 */
	public function getCount()
	{
		return $this->data_source->count($this->primary_key);
	}


	/**
	 * @return array
	 */
	public function getData()
	{
		return $this->data ?: $this->data_source->fetchAll();
	}


	/**
	 * @param array $filters
	 * @return void
	 */
	public function filter(array $filters)
	{
		foreach ($filters as $filter) {
			if ($filter->isValueSet()) {
				$or = [];

				if ($filter->hasConditionCallback()) {
					Callback::invokeArgs(
						$filter->getConditionCallback(),
						[$this->data_source, $filter->getValue()]
					);
				} else {
					if ($filter instanceof Filter\FilterText) {
						$this->applyFilterText($filter);
					} else if ($filter instanceof Filter\FilterSelect) {
						$this->applyFilterSelect($filter);
					} else if ($filter instanceof Filter\FilterDateTime) {
						$this->applyFilterDateTime($filter);
					} else if ($filter instanceof Filter\FilterDateRange) {
						$this->applyFilterDateRange($filter);
					} else if ($filter instanceof Filter\FilterRange) {
						$this->applyFilterRange($filter);
					}
				}
			}
		}

		return $this;
	}


	/**
	 * @param array $filter
	 * @return void
	 */
	public function filterOne(array $filter)
	{
		$this->data_source->where($filter)->limit(1);

		return $this;
	}


	public function applyFilterDateRange(Filter\FilterDateRange $filter)
	{
		$conditions = $filter->getCondition();

		$value_from = $conditions[$filter->getColumn()]['from'];
		$value_to   = $conditions[$filter->getColumn()]['to'];

		
		$date_timestamp_to = $value_to ? strtotime($value_to) : NULL;

		if ($value_from) {
			$date_timestamp_from = strtotime($value_from);

			$this->data_source->where('DATE(%n) >= ?', $filter->getColumn(), date('Y-m-d', $date_timestamp_from));
		}

		if ($value_to) {
			$date_timestamp_from = strtotime($value_to);

			$this->data_source->where('DATE(%n) <= ?', $filter->getColumn(), date('Y-m-d', $date_timestamp_to));
		}
	}


	public function applyFilterRange(Filter\FilterRange $filter)
	{
		$conditions = $filter->getCondition();

		$value_from = $conditions[$filter->getColumn()]['from'];
		$value_to   = $conditions[$filter->getColumn()]['to'];

		if ($value_from) {
			$this->data_source->where('%n >= ?', $filter->getColumn(), $value_from);
		}

		if ($value_to) {
			$this->data_source->where('%n <= ?', $filter->getColumn(), $value_to);
		}
	}


	public function applyFilterText(Filter\FilterText $filter)
	{
		$condition = $filter->getCondition();

		foreach ($condition as $column => $value) {
			$words = explode(' ', $value);

			foreach ($words as $word) {
				$escaped = $this->data_source->getConnection()->getDriver()->escapeLike($word, 0);

				if (preg_match("/[\x80-\xFF]/", $escaped)) {
					$or[] = "$column LIKE $escaped COLLATE utf8_bin";
				} else {
					$escaped = Strings::toAscii($escaped);
					$or[] = "$column LIKE $escaped COLLATE utf8_general_ci";
				}
			}
		}

		if (sizeof($or) > 1) {
			$this->data_source->where('(%or)', $or);
		} else {
			$this->data_source->where($or);
		}
	}


	public function applyFilterSelect(Filter\FilterSelect $filter)
	{
		$this->data_source->where($filter->getCondition());
	}


	public function applyFilterDateTime(Filter\FilterDateTime $filter)
	{
		$conditions = $filter->getCondition();

		$date_timestamp = strtotime($conditions[$filter->getColumn()]);

		$this->data_source->where('DATE(%n) = ?', $filter->getColumn(), date('Y-m-d', $date_timestamp));
	}


	/**
	 * @param int $offset
	 * @param int $limit
	 * @return void
	 */
	public function limit($offset, $limit)
	{
		$this->data = $this->data_source->limit($limit, $offset)->fetchAll();

		return $this;
	}


	public function sort(array $sorting)
	{
		if ($sorting) {
			$this->data_source->getSqlBuilder()->setOrder([], []);

			foreach ($sorting as $column => $sort) {
				$this->data_source->order("$column $sort");
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

}
