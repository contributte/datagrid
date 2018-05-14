<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\DataSource;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Ublaboo\DataGrid\AggregationFunction\IAggregatable;
use Ublaboo\DataGrid\Filter;
use Ublaboo\DataGrid\Utils\DateTimeHelper;
use Ublaboo\DataGrid\Utils\Sorting;

final class DoctrineCollectionDataSource extends FilterableDataSource implements IDataSource, IAggregatable
{

	/**
	 * @var Collection
	 */
	protected $data_source;

	/**
	 * @var string
	 */
	protected $primary_key;

	/**
	 * @var Criteria
	 */
	protected $criteria;

	public function __construct(Collection $collection, string $primary_key)
	{
		$this->criteria = Criteria::create();
		$this->data_source = $collection;
		$this->primary_key = $primary_key;
	}


	private function getFilteredCollection(): Collection
	{
		return $this->data_source->matching($this->criteria);
	}


	/********************************************************************************
	 *                          IDataSource implementation                          *
	 ********************************************************************************/

	/**
	 * Get count of data
	 */
	public function getCount(): int
	{
		return $this->getFilteredCollection()->count();
	}


	/**
	 * Get the data
	 *
	 * @return array
	 */
	public function getData(): array
	{
		return $this->getFilteredCollection()->toArray();
	}


	/**
	 * Filter data - get one row
	 *
	 * @param array $condition
	 * @return static
	 */
	public function filterOne(array $condition)
	{
		foreach ($condition as $column => $value) {
			$expr = Criteria::expr()->eq($column, $value);
			$this->criteria->andWhere($expr);
		}

		return $this;
	}


	/**
	 * Filter by date
	 */
	public function applyFilterDate(Filter\FilterDate $filter): void
	{
		foreach ($filter->getCondition() as $column => $value) {
			$date = DateTimeHelper::tryConvertToDateTime($value, [$filter->getPhpFormat()]);

			$from = Criteria::expr()->gte($filter->getColumn(), $date->format('Y-m-d 00:00:00'));
			$to = Criteria::expr()->lte($filter->getColumn(), $date->format('Y-m-d 23:59:59'));

			$this->criteria->andWhere($from)->andWhere($to);
		}
	}


	/**
	 * Filter by date range
	 */
	public function applyFilterDateRange(Filter\FilterDateRange $filter): void
	{
		$conditions = $filter->getCondition();
		$values = $conditions[$filter->getColumn()];

		if ($value_from = $values['from']) {
			$date_from = DateTimeHelper::tryConvertToDateTime($value_from, [$filter->getPhpFormat()]);
			$date_from->setTime(0, 0, 0);

			$expr = Criteria::expr()->gte($filter->getColumn(), $date_from->format('Y-m-d H:i:s'));
			$this->criteria->andWhere($expr);
		}

		if ($value_to = $values['to']) {
			$date_to = DateTimeHelper::tryConvertToDateTime($value_to, [$filter->getPhpFormat()]);
			$date_to->setTime(23, 59, 59);

			$expr = Criteria::expr()->lte($filter->getColumn(), $date_to->format('Y-m-d H:i:s'));
			$this->criteria->andWhere($expr);
		}
	}


	/**
	 * Filter by range
	 */
	public function applyFilterRange(Filter\FilterRange $filter): void
	{
		$conditions = $filter->getCondition();
		$values = $conditions[$filter->getColumn()];

		if ($value_from = $values['from']) {
			$expr = Criteria::expr()->gte($filter->getColumn(), $value_from);
			$this->criteria->andWhere($expr);
		}

		if ($value_to = $values['to']) {
			$expr = Criteria::expr()->lte($filter->getColumn(), $value_to);
			$this->criteria->andWhere($expr);
		}
	}


	/**
	 * Filter by keyword
	 */
	public function applyFilterText(Filter\FilterText $filter): void
	{
		$exprs = [];

		foreach ($filter->getCondition() as $column => $value) {
			if ($filter->isExactSearch()) {
				$exprs[] = Criteria::expr()->eq($column, $value);
				continue;
			}

			if ($filter->hasSplitWordsSearch() === false) {
				$words = [$value];
			} else {
				$words = explode(' ', $value);
			}

			foreach ($words as $word) {
				$exprs[] = Criteria::expr()->contains($column, $word);
			}
		}

		$expr = call_user_func_array([Criteria::expr(), 'orX'], $exprs);
		$this->criteria->andWhere($expr);
	}


	/**
	 * Filter by multi select value
	 */
	public function applyFilterMultiSelect(Filter\FilterMultiSelect $filter): void
	{
		$values = $filter->getCondition()[$filter->getColumn()];

		$expr = Criteria::expr()->in($filter->getColumn(), $values);
		$this->criteria->andWhere($expr);
	}


	/**
	 * Filter by select value
	 */
	public function applyFilterSelect(Filter\FilterSelect $filter): void
	{
		foreach ($filter->getCondition() as $column => $value) {
			$expr = Criteria::expr()->eq($column, $value);
			$this->criteria->andWhere($expr);
		}
	}


	/**
	 * Apply limit and offset on data
	 *
	 * @return static
	 */
	public function limit(int $offset, int $limit)
	{
		$this->criteria->setFirstResult($offset)->setMaxResults($limit);

		return $this;
	}


	/**
	 * Sort data
	 *
	 * @return static
	 */
	public function sort(Sorting $sorting)
	{
		if (is_callable($sorting->getSortCallback())) {
			call_user_func(
				$sorting->getSortCallback(),
				$this->criteria,
				$sorting->getSort()
			);

			return $this;
		}

		if ($sort = $sorting->getSort()) {
			$this->criteria->orderBy($sort);
			return $this;
		}

		$this->criteria->orderBy([$this->primary_key => 'ASC']);

		return $this;
	}


	public function processAggregation(callable $aggregationCallback): void
	{
		call_user_func($aggregationCallback, clone $this->data_source);
	}

}
