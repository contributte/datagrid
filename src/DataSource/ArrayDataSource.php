<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\DataSource;

use Ublaboo\DataGrid\Filter\Filter;
use Ublaboo\DataGrid\Filter\FilterDate;
use Nette\Utils\Callback;
use Nette\Utils\Strings;
use Ublaboo\DataGrid\Exception\DataGridException;
use Ublaboo\DataGrid\Utils\DateTimeHelper;
use Ublaboo\DataGrid\Exception\DataGridDateTimeHelperException;

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
	 * @return static
	 */
	public function filter(array $filters)
	{
		foreach ($filters as $filter) {
			if ($filter->isValueSet()) {
				if ($filter->hasConditionCallback()) {
					$this->data = call_user_func_array(
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
	 * @return static
	 */
	public function limit($offset, $limit)
	{
		$this->data = array_slice($this->data, $offset, $limit);

		return $this;
	}

	/**
	 * Order data
	 * @param array $sorting
	 * @return static
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
			if ($filter instanceof FilterDate) {
				return $this->applyFilterDate($row, $filter);
			}

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


	/**
	 * Apply fitler date and tell whether row value matches or not
	 * @param  mixed  $row
	 * @param  Filter $filter
	 * @return mixed
	 */
	protected function applyFilterDate($row, FilterDate $filter)
	{
		$format = $filter->getPhpFormat();
		$condition = $filter->getCondition();

		foreach ($condition as $column => $value) {
			$row_value = $row[$column];

			$date = \DateTime::createFromFormat($format, $value);

			if (!($row_value instanceof DateTime)) {
				/**
				 * Try to convert string to DateTime object
				 */
				try {
					$row_value = DateTimeHelper::tryConvertToDateTime($row_value);
				} catch (DataGridDateTimeHelperException $e) {
					/**
					 * Otherwise just return raw string
					 */
					return FALSE;
				}
			}

			return $row_value->format($format) == $date->format($format);
		}
	}

}
