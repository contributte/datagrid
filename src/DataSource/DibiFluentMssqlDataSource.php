<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\DataSource;

use DibiFluent,
	Nette\Utils\Callback,
	Ublaboo\DataGrid\Filter;

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
		$clone = clone $this->data_source;
		$clone->removeClause('ORDER BY');

		return $clone->count();
	}


	/**
	 * @param array $condition
	 * @return void
	 */
	public function filterOne(array $condition)
	{
		$this->data_source->where($condition);

		return $this;
	}


	public function applyFilterDate(Filter\FilterDate $filter)
	{
		$conditions = $filter->getCondition();

		$date = \DateTime::createFromFormat($filter->getPhpFormat(), $conditions[$filter->getColumn()]);

		$ymd = $date->format('Ymd');

		$this->data_source->where('CONVERT(varchar(10), %n, 112) = ?', $filter->getColumn(), $ymd);
	}


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


	public function applyFilterText(Filter\FilterText $filter)
	{
		$condition = $filter->getCondition();

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
	 * @param int $offset
	 * @param int $limit
	 * @return void
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
