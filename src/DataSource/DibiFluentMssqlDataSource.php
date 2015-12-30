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
	 * @param array $filter
	 * @return void
	 */
	public function filterOne(array $filter)
	{
		$this->data_source->where($filter);

		return $this;
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
