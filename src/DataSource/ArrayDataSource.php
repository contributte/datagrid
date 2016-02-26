<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\DataSource;

use Ublaboo\DataGrid\Filter\Filter;
use Nette\Utils\Callback;
use Nette\Utils\Strings;
use Ublaboo\DataGrid\Exception\DataGridException;

class ArrayDataSource implements IDataSource
{

	/**
	 * @var array
	 */
	protected $data = [];


	/**
	 * @param array $data_source
	 */
	public function __construct(array $data_source)
	{
		$this->data = $data_source;
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
		return sizeof($this->data);
	}


	/**
	 * Get the data
	 * @return array
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}


	/**
	 * Filter data
	 * @param Filter[] $filters
	 * @return self
	 */
	public function filter(array $filters)
	{
		foreach ($filters as $filter) {
			if ($filter->isValueSet()) {
				if ($filter->hasConditionCallback()) {
					$this->data = Callback::invokeArgs(
						$filter->getConditionCallback(),
						[$this->data, $filter->getValue()]
					);
				} else {
					$this->data = array_filter($this->data, function($row) use ($filter) {
						return $this->applyFilter($row, $filter);
					});
				}
			}
		}
		
		return $this;
	}


	/**
	 * Filter data - get one row
	 * @param array $condition
	 * @return ArrayDataSource
	 */
	public function filterOne(array $condition)
	{
		foreach ($this->data as $item) {
			foreach ($condition as $key => $value) {
				if ($item[$key] == $value) {
					$this->data = [$item];

					return $this;
				}
			}
		}

		$this->data = [];

		return $this;
	}


	/**
	 * Apply limit and offet on data
	 * @param int $offset
	 * @param int $limit
	 * @return self
	 */
	public function limit($offset, $limit)
	{
		$this->data = array_slice($this->data, $offset, $limit);

		return $this;
	}

	/**
	 * Order data
	 * @param array $sorting
	 * @return self
	 */
	public function sort(array $sorting)
	{
		/**
		 * Taken from Grido
		 * @todo Not tested yet
		 */
		if (sizeof($sorting) > 1) {
			throw new DataGridException('Multi-column sorting is not implemented yet.');
		}

		foreach ($sorting as $column => $sort) {
			$data = array();
			foreach ($this->data as $item) {
				$sorter = (string) $item[$column];
				$data[$sorter][] = $item;
			}

			if ($sort === 'ASC') {
				ksort($data);
			} else {
				krsort($data);
			}

			$this->data = array();
			foreach ($data as $i) {
				foreach ($i as $item) {
					$this->data[] = $item;
				}
			}
		}

		return $this;
	}


	/**
	 * Apply fitler and tell whether row passes conditions or not
	 * @param  mixed  $row
	 * @param  Filter $filter
	 * @return mixed
	 */
	protected function applyFilter($row, Filter $filter)
	{
		if (is_array($row) || $row instanceof \Traversable) {
			$condition = $filter->getCondition();

			foreach ($condition as $column => $value) {
				$words = explode(' ', $value);
				$row_value = strtolower(Strings::toAscii($row[$column]));

				foreach ($words as $word) {
					if (FALSE !== strpos($row_value, strtolower(Strings::toAscii($value)))) {
						return $row;
					}
				}
			}
		}

		return FALSE;
	}

}
