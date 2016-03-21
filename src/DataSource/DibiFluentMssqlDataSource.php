<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\DataSource;

use DibiFluent;
use Ublaboo\DataGrid\Filter;

class DibiFluentMssqlDataSource extends DibiFluentDataSource
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


	/**
	 * @param DibiFluent $data_source
	 * @param string $primary_key
	 */
	public function __construct(DibiFluent $data_source, $primary_key)
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
		$clone = clone $this->data_source;
		$clone->removeClause('ORDER BY');

		return $clone->count();
	}


	/**
	 * Get the data
	 * @param array $condition
	 * @return static
	 */
	public function filterOne(array $condition)
	{
		$this->data_source->where($condition);

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

		$date = \DateTime::createFromFormat($filter->getPhpFormat(), $conditions[$filter->getColumn()]);

		$ymd = $date->format('Ymd');

		$this->data_source->where('CONVERT(varchar(10), %n, 112) = ?', $filter->getColumn(), $ymd);
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
		$value_to   = $conditions[$filter->getColumn()]['to'];

		if ($value_from) {
			$this->data_source->where('CONVERT(varchar(10), %n, 112) >= ?', $filter->getColumn(), $value_from);
		}

		if ($value_to) {
			$this->data_source->where('CONVERT(varchar(10), %n, 112) <= ?', $filter->getColumn(), $value_to);
		}
	}


	/**
	 * Filter by date
	 * @param  Filter\FilterText $filter
	 * @return void
	 */
	public function applyFilterText(Filter\FilterText $filter)
	{
		$condition = $filter->getCondition();
		$or = [];

		foreach ($condition as $column => $value) {
			$or[] = "$column LIKE \"%$value%\"";
		}

		if (sizeof($or) > 1) {
			$this->data_source->where('(%or)', $or);
		} else {
			$this->data_source->where($or);
		}
	}


	/**
	 * Apply limit and offset on data
	 * @param int $offset
	 * @param int $limit
	 * @return static
	 */
	public function limit($offset, $limit)
	{
		$sql = (string) $this->data_source;

		$result = $this->data_source->getConnection()
			->query('%sql OFFSET ? ROWS FETCH NEXT ? ROWS ONLY', $sql, $offset, $limit);

		$this->data = $result->fetchAll();

		return $this;
	}

}
