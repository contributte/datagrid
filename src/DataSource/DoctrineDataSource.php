<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Jakub Kontra <me@jakubkontra.cz>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\DataSource;

use Doctrine\ORM\QueryBuilder,
	Ublaboo\DataGrid\Filter,
	Nette\Utils\Callback,
	Nette\Utils\Strings,
	Doctrine;

class DoctrineDataSource implements IDataSource
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
	 * Filter data
	 * @param array $filters
	 * @return self
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
					} else if ($filter instanceof Filter\FilterDate) {
						$this->applyFilterDate($filter);
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
	 * Filter data - get one row
	 * @param array $condition
	 * @return self
	 */
	public function filterOne(array $condition)
	{
		$p = $this->getPlaceholder();

		foreach ($condition as $column => $value) {
			$this->data_source->andWhere("$column = ?$p")
				->setParameter($p, $value);
		}

		return $this;
	}


	/**
	 * Filter by date
	 * @param  Filter\FilterDate $filter
	 * @return void
	 */
	public function applyFilterDate(Filter\FilterDate $filter)
	{
		$p1 = $this->getPlaceholder();
		$p2 = $this->getPlaceholder();

		foreach ($filter->getCondition() as $column => $value) {
			$date = \DateTime::createFromFormat($filter->getPhpFormat(), $value);

			$this->data_source
				->andWhere("$column >= ?$p1")
				->andWhere("$column <= ?$p2")
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
		$column = $filter->getColumn();

		$value_from = $conditions[$filter->getColumn()]['from'];
		$value_to   = $conditions[$filter->getColumn()]['to'];

		if ($value_from) {
			$date_from = \DateTime::createFromFormat($filter->getPhpFormat(), $value_from);
			$date_from->setTime(0, 0, 0);

			$p = $this->getPlaceholder();

			$this->data_source->andWhere("$column >= ?$p")->setParameter($p, $date_from->format('Y-m-d H:i:s'));
		}

		if ($value_to) {
			$date_to = \DateTime::createFromFormat($filter->getPhpFormat(), $value_to);
			$date_to->setTime(23, 59, 59);

			$p = $this->getPlaceholder();

			$this->data_source->andWhere("$column <= ?$p")->setParameter($p, $date_to->format('Y-m-d H:i:s'));
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
		$column = $filter->getColumn();

		$value_from = $conditions[$filter->getColumn()]['from'];
		$value_to   = $conditions[$filter->getColumn()]['to'];

		if ($value_from) {
			$p = $this->getPlaceholder();
			$this->data_source->andWhere("$column >= ?$p")->setParameter($p, $value_from);
		}

		if ($value_to) {
			$p = $this->getPlaceholder();
			$this->data_source->andWhere("$column <= ?$p")->setParameter($p, $value_to);
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

		foreach ($condition as $column => $value) {
			$words = explode(' ', $value);

			foreach ($words as $word) {
				$exprs[] = $this->data_source->expr()->like($column, $this->data_source->expr()->literal("%$word%"));

				/**
				 * @todo Manage somehow COLLATE statement in DQL
				 */
				/*if (preg_match("/[\x80-\xFF]/", $word)) {
					$or[] = "$column LIKE $escaped COLLATE utf8_bin";
				} else {
					$escaped = Strings::toAscii($escaped);
					$or[] = "$column LIKE $escaped COLLATE utf8_general_ci";
				}*/
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
			$this->data_source->andWhere("$column = ?$p")
				->setParameter($p, $value);
		}
	}


	/**
	 * Apply limit and offet on data
	 * @param int $offset
	 * @param int $limit
	 * @return self
	 */
	public function limit($offset, $limit)
	{
		$this->data_source->setFirstResult($offset)->setMaxResults($limit);

		return $this;
	}


	/**
	 * Order data
	 * @param  array  $sorting
	 * @return self
	 */
	public function sort(array $sorting)
	{
		$alias = current($this->data_source->getDQLPart('from'))->getAlias();

		if ($sorting) {
			foreach ($sorting as $column => $sort) {
				$this->data_source->orderBy("{$alias}.{$column}", $sort);
			}
		} else {
			/**
			 * Has the statement already a order by clause?
			 */
			if (!$this->data_source->getDQLPart('orderBy')) {
				$this->data_source->orderBy("{$alias}.{$this->primary_key}");
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
