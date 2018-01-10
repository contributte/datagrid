<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\DataSource;

use Nette\Utils\Strings;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Mapper\Dbal\DbalCollection;
use Ublaboo\DataGrid\Filter;
use Ublaboo\DataGrid\Utils\ArraysHelper;
use Ublaboo\DataGrid\Utils\DateTimeHelper;
use Ublaboo\DataGrid\Utils\Sorting;

class NextrasDataSource extends FilterableDataSource implements IDataSource
{

	/**
	 * @var DbalCollection
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
	 * @param ICollection  $data_source
	 * @param string       $primary_key
	 */
	public function __construct(ICollection $data_source, $primary_key)
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
		return $this->data_source->countStored();
	}


	/**
	 * Get the data
	 * @return array
	 */
	public function getData()
	{
		/**
		 * Paginator is better if the query uses ManyToMany associations
		 */
		return $this->data ?: $this->data_source->fetchAll();
	}


	/**
	 * Filter data - get one row
	 * @param array $condition
	 * @return static
	 */
	public function filterOne(array $condition)
	{
		$cond = [];
		foreach ($condition as $key => $value) {
			$cond[$this->prepareColumn($key)] = $value;
		}
		$this->data_source = $this->data_source->findBy($cond);

		return $this;
	}


	/**
	 * Filter by date
	 * @param  Filter\FilterDate $filter
	 * @return static
	 */
	public function applyFilterDate(Filter\FilterDate $filter)
	{
		foreach ($filter->getCondition() as $column => $value) {
			$date = DateTimeHelper::tryConvertToDateTime($value, [$filter->getPhpFormat()]);
			$date_end = clone $date;

			$this->data_source = $this->data_source->findBy([
				$this->prepareColumn($column) . '>=' => $date->setTime(0, 0, 0),
				$this->prepareColumn($column) . '<=' => $date_end->setTime(23, 59, 59),
			]);
		}

		return $this;
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

		$dataCondition = [];
		if ($value_from) {
			$date_from = DateTimeHelper::tryConvertToDateTime($value_from, [$filter->getPhpFormat()]);
			$dataCondition[$this->prepareColumn($filter->getColumn()) . '>='] = $date_from->setTime(0, 0, 0);
		}

		if ($value_to) {
			$date_to = DateTimeHelper::tryConvertToDateTime($value_to, [$filter->getPhpFormat()]);
			$dataCondition[$this->prepareColumn($filter->getColumn()) . '<='] = $date_to->setTime(23, 59, 59);
		}

		if (!empty($dataCondition)) {
			$this->data_source = $this->data_source->findBy($dataCondition);
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

		$dataCondition = [];

		if ($value_from) {
			$dataCondition[$this->prepareColumn($filter->getColumn()) . '>='] = $value_from;
		}

		if ($value_to) {
			$dataCondition[$this->prepareColumn($filter->getColumn()) . '<='] = $value_to;
		}

		if (!empty($dataCondition)) {
			$this->data_source = $this->data_source->findBy($dataCondition);
		}
	}


	/**
	 * Filter by keyword
	 * @param  Filter\FilterText $filter
	 * @return void
	 */
	public function applyFilterText(Filter\FilterText $filter)
	{
		$condition = $filter->getCondition();
		$expr = '(';
		$params = [];

		foreach ($condition as $column => $value) {
			if ($filter->isExactSearch()) {
				$expr .= '%column = %s OR ';
				$params[] = $column;
				$params[] = "$value";
				continue;
			}

			if ($filter->hasSplitWordsSearch() === false) {
				$words = [$value];
			} else {
				$words = explode(' ', $value);
			}

			foreach ($words as $word) {
				$expr .= '%column LIKE %s OR ';
				$params[] = $column;
				$params[] = "%$word%";
			}
		}

		$expr = preg_replace('/ OR $/', ')', $expr);

		array_unshift($params, $expr);

		call_user_func_array([$this->data_source->getQueryBuilder(), 'andWhere'], $params);
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
		$expr = '(';

		foreach ($values as $value) {
			$expr .= '%column = %any OR ';
			$params[] = $filter->getColumn();
			$params[] = "$value";
		}

		$expr = preg_replace('/ OR $/', ')', $expr);

		array_unshift($params, $expr);

		call_user_func_array([$this->data_source->getQueryBuilder(), 'andWhere'], $params);
	}


	/**
	 * Filter by select value
	 * @param  Filter\FilterSelect $filter
	 * @return void
	 */
	public function applyFilterSelect(Filter\FilterSelect $filter)
	{
		$this->data_source = $this->data_source->findBy([$this->prepareColumn($filter->getColumn()) => $filter->getValue()]);
	}


	/**
	 * Apply limit and offset on data
	 * @param int $offset
	 * @param int $limit
	 * @return static
	 */
	public function limit($offset, $limit)
	{
		$this->data_source = $this->data_source->limitBy($limit, $offset);

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
			foreach ($sort as $column => $order) {
				$this->data_source = $this->data_source->orderBy($this->prepareColumn($column), $order);
			}
		} else {
			/**
			 * Has the statement already a order by clause?
			 */
			$order = $this->data_source->getQueryBuilder()->getClause('order');

			if (ArraysHelper::testEmpty($order)) {
				$this->data_source = $this->data_source->orderBy($this->primary_key);
			}
		}

		return $this;
	}


		/**
		 * Adjust column from DataGrid 'foreignKey.column' to Nextras 'this->foreignKey->column'
		 * @param string $column
		 * @return string
		 */
		private function prepareColumn($column)
		{
			if (Strings::contains($column, '.')) {
				return 'this->' . str_replace('.', '->', $column);
			}
			return $column;
		}
}
