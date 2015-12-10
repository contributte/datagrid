<?php

/**
 * * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\DataSource;

use DibiFluent;

class ArrayDataSource
{

	/**
	 * @var array
	 */
	protected $data = [];


	public function __construct($data_source)
	{
		$this->data = $data_source;
	}


	/********************************************************************************
	 *                          IDataSource implementation                          *
	 ********************************************************************************/


	/**
	 * @return int
	 */
	public function getCount()
	{
		return sizeof($this->data);
	}


	/**
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}


	/**
	 * @param array $filters
	 */
	public function filter(array $filters)
	{
		foreach ($filters as $filter) {
			if ($filter->isValueSet()) {
				$or = [];

				/**
				 * @var array|callable
				 */
				$condition = $filter->getCondition();

				if (is_callable($condition)) {
					Callback::invokeArgs($condition, [$this->data_source, $filter->getValue()]);
				} else {
					/**
					 * @todo
					 */
					$this->data = $this->makeWhere($condition);
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
		$this->data = $this->makeWhere($filter);

		return $this;
	}


	/**
	 * @param int $offset
	 * @param int $limit
	 */
	public function limit($offset, $limit)
	{
		$this->data = array_slice($this->data, $offset, $limit);

		return $this;
	}

	/**
	 * @param array $sorting
	 */
	public function sort(array $sorting)
	{
		/**
		 * Taken from Grido
		 * @todo Not tested yet
		 */
		if (sizeof($sorting) > 1) {
			throw new Ublaboo\DataGrid\DataGridException('Multi-column sorting is not implemented yet.');
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
			foreach($data as $i) {
				foreach($i as $item) {
					$this->data[] = $item;
				}
			}
		}

		return $this;
	}


	/********************************************************************************
	 *                                Support methods                               *
	 ********************************************************************************/


	/**
	 * @param Condition $condition
	 * @param array $data
	 * @return array
	 */
	protected function makeWhere(Condition $condition, array $data = NULL)
	{
		/**
		 * Taken from Grido
		 * @todo Not tested yet
		 */
		$data = $data === NULL
			? $this->data
			: $data;

		$compare = [$this, 'compare'];
		return array_filter($data, function ($row) use ($condition, $compare) {
			if ($condition->callback) {
				return callback($condition->callback)->invokeArgs(array($condition->value, $row));
			}

			$i = 0;
			$results = array();
			foreach ($condition->column as $column) {
				if (Condition::isOperator($column)) {
					$results[] = " $column ";

				} else {
					$i = count($condition->condition) > 1 ? $i : 0;
					$results[] = (int) $compare(
						$row[$column],
						$condition->condition[$i],
						isset($condition->value[$i])
							? $condition->value[$i]
							: NULL
					);

					$i++;
				}
			}

			$result = implode('', $results);
			return count($condition->column) === 1
				? (bool) $result
				: eval("return $result;");
		});
	}

	/**
	 * @param string $actual
	 * @param string $condition
	 * @param mixed $expected
	 * @throws \InvalidArgumentException
	 * @return bool
	 */
	public function compare($actual, $condition, $expected)
	{
		/**
		 * Taken from Grido
		 * @todo Not tested yet
		 */
		$expected = current((array) $expected);
		$cond = str_replace(' ?', '', $condition);

		if ($cond === 'LIKE') {
			$pattern = str_replace('%', '.*', preg_quote($expected));
			return (bool) preg_match("/^{$pattern}$/i", $actual);

		} else if ($cond === '=') {
			return $actual == $expected;

		} else if ($cond === '<>') {
			return $actual != $expected;

		} elseif ($cond === 'IS NULL') {
			return $actual === NULL;

		} elseif ($cond === 'IS NOT NULL') {
			return $actual !== NULL;

		} elseif (in_array($cond, array('<', '<=', '>', '>='))) {
			return eval("return {$actual} {$cond} {$expected};");

		} else {
			throw new \InvalidArgumentException("Condition '$condition' not implemented yet.");
		}
	}

}
