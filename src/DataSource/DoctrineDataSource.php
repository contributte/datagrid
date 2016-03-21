<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Jakub Kontra <me@jakubkontra.cz>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\DataSource;

use Doctrine\ORM\QueryBuilder;
use Ublaboo\DataGrid\Filter;
use Nette\Utils\Callback;
use Nette\Utils\Strings;
use Doctrine;
use Ublaboo\DataGrid\Utils\Sorting;

class DoctrineDataSource extends FilterableDataSource implements IDataSource
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
	 * @var int
	 */
	protected $placeholder = 0;


	/**
	 * @param QueryBuilder $data_source
	 * @param string       $primary_key
	 */
	public function __construct(QueryBuilder $data_source, $primary_key)
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


	private function checkAliases($column)
	{
		if (Strings::contains($column, ".")) {
			return $column;
		}

		return current($this->data_source->getRootAliases()) . '.' . $column;
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
		$paginator = new Doctrine\ORM\Tools\Pagination\Paginator($this->getQuery());
		$count = count($paginator);

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
		return $this->data ?: $this->data_source->getQuery()->getResult();
	}


	/**
	 * Filter data - get one row
	 * @param array $condition
	 * @return static
	 */
	public function filterOne(array $condition)
	{
		$p = $this->getPlaceholder();

		foreach ($condition as $column => $value) {
			$c = $this->checkAliases($column);

			$this->data_source->andWhere("$c = ?$p")
				->setParameter($p, $value);
		}

		return $this;
	}


	/**
	 * Filter by date
	 * @param  Filter\FilterDate $filter
	 * @return static
	 */
	public function applyFilterDate(Filter\FilterDate $filter)
	{
		$p1 = $this->getPlaceholder();
		$p2 = $this->getPlaceholder();

		foreach ($filter->getCondition() as $column => $value) {
			$date = \DateTime::createFromFormat($filter->getPhpFormat(), $value);
			$c = $this->checkAliases($column);

			$this->data_source
				->andWhere("$c >= ?$p1")
				->andWhere("$c <= ?$p2")
				->setParameter($p1, $date->format('Y-m-d 00:00:00'))
				->setParameter($p2, $date->format('Y-m-d 23:59:59'));
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
		$c = $this->checkAliases($filter->getColumn());

		$value_from = $conditions[$filter->getColumn()]['from'];
		$value_to   = $conditions[$filter->getColumn()]['to'];

		if ($value_from) {
			$date_from = \DateTime::createFromFormat($filter->getPhpFormat(), $value_from);
			$date_from->setTime(0, 0, 0);

			$p = $this->getPlaceholder();

			$this->data_source->andWhere("$c >= ?$p")->setParameter($p, $date_from->format('Y-m-d H:i:s'));
		}

		if ($value_to) {
			$date_to = \DateTime::createFromFormat($filter->getPhpFormat(), $value_to);
			$date_to->setTime(23, 59, 59);

			$p = $this->getPlaceholder();

			$this->data_source->andWhere("$c <= ?$p")->setParameter($p, $date_to->format('Y-m-d H:i:s'));
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
		$c = $this->checkAliases($filter->getColumn());

		$value_from = $conditions[$filter->getColumn()]['from'];
		$value_to   = $conditions[$filter->getColumn()]['to'];

		if ($value_from) {
			$p = $this->getPlaceholder();
			$this->data_source->andWhere("$c >= ?$p")->setParameter($p, $value_from);
		}

		if ($value_to) {
			$p = $this->getPlaceholder();
			$this->data_source->andWhere("$c <= ?$p")->setParameter($p, $value_to);
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
		$exprs = [];

		foreach ($condition as $column => $value) {
			$words = explode(' ', $value);
			$c = $this->checkAliases($column);

			foreach ($words as $word) {
				$exprs[] = $this->data_source->expr()->like($c, $this->data_source->expr()->literal("%$word%"));
			}
		}

		$or = call_user_func_array([$this->data_source->expr(), 'orX'], $exprs);

		$this->data_source->andWhere($or);
	}


	/**
	 * Filter by select value
	 * @param  Filter\FilterSelect $filter
	 * @return void
	 */
	public function applyFilterSelect(Filter\FilterSelect $filter)
	{
		$p = $this->getPlaceholder();

		foreach ($filter->getCondition() as $column => $value) {
			$c = $this->checkAliases($column);

			$this->data_source->andWhere("$c = ?$p")
				->setParameter($p, $value);
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
		$this->data_source->setFirstResult($offset)->setMaxResults($limit);

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
				$this->data_source->addOrderBy($this->checkAliases($column), $order);
			}
		} else {
			/**
			 * Has the statement already a order by clause?
			 */
			if (!$this->data_source->getDQLPart('orderBy')) {
				$this->data_source->orderBy($this->checkAliases($this->primary_key));
			}
		}

		return $this;
	}


	/**
	 * Get unique int value for each instance class (self)
	 * @return int
	 */
	public function getPlaceholder()
	{
		$this->placeholder++;

		return $this->placeholder;
	}

}
