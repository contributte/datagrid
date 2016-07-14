<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\DataSource;

use Ublaboo\DataGrid\Filter;
use Nette\Utils\Callback;
use Nette\Utils\Strings;
use Ublaboo\DataGrid\Utils\Sorting;
use Nextras\Orm\Collection\ICollection;
use Ublaboo\DataGrid\Utils\ArraysHelper;

class NextrasDataSource extends FilterableDataSource implements IDataSource
{

	/**
	 * @var QueryBuilder
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


	/**
	 * @return Doctrine\ORM\Query
	*/
	public function getQuery()
	{
		return $this->data_source->getQuery();
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

		return $count;
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
		$this->data_source = $this->data_source->findBy($condition);

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
			$date = \DateTime::createFromFormat($filter->getPhpFormat(), $value);
			$date_end = clone $date;

			$this->data_source->getQueryBuilder()
				->andWhere("%column >= %dt", $column, $date->setTime(0, 0, 0))
				->andWhere("%column <= %dt", $column, $date_end->setTime(23, 59, 59));
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
		$value_to   = $conditions[$filter->getColumn()]['to'];

		if ($value_from) {
			$date_from = \DateTime::createFromFormat($filter->getPhpFormat(), $value_from);
			$date_from;

			$this->data_source->getQueryBuilder()->andWhere("%column >= %dt", $filter->getColumn(), $date_from);
		}

		if ($value_to) {
			$date_to = \DateTime::createFromFormat($filter->getPhpFormat(), $value_to);
			$date_to->setTime(23, 59, 59);

			$this->data_source->getQueryBuilder()->andWhere("%column <= %dt", $filter->getColumn(), $date_to);
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
		$value_to   = $conditions[$filter->getColumn()]['to'];

		if ($value_from) {
			$this->data_source->getQueryBuilder()->andWhere("%column >= %any", $filter->getColumn(), $value_from);
		}

		if ($value_to) {
			$this->data_source->getQueryBuilder()->andWhere("%column <= %any", $filter->getColumn(), $value_to);
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
			if ($filter->hasSplitWordsSearch() === FALSE) {
				$words = [$value];
			} else {
				$words = explode(' ', $value);
			}

			foreach ($words as $word) {
				$expr .= "%column LIKE %s OR ";
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
			$expr .= "%column = %any OR ";
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
		foreach ($filter->getCondition() as $column => $value) {
			$this->data_source->getQueryBuilder()->andWhere("%column = %any", $column, $value);
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
				$this->data_source = $this->data_source->orderBy($column, $order);
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

}
