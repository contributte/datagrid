<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\DataSource;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Ublaboo\DataGrid\AggregationFunction\IAggregatable;
use Ublaboo\DataGrid\AggregationFunction\IAggregationFunction;
use Ublaboo\DataGrid\Exception\DataGridDateTimeHelperException;
use Ublaboo\DataGrid\Filter\FilterDate;
use Ublaboo\DataGrid\Filter\FilterDateRange;
use Ublaboo\DataGrid\Filter\FilterMultiSelect;
use Ublaboo\DataGrid\Filter\FilterRange;
use Ublaboo\DataGrid\Filter\FilterSelect;
use Ublaboo\DataGrid\Filter\FilterText;
use Ublaboo\DataGrid\Utils\DateTimeHelper;
use Ublaboo\DataGrid\Utils\Sorting;

final class DoctrineCollectionDataSource extends FilterableDataSource implements
	IDataSource,
	IAggregatable
{

	/**
	 * @var Collection&Selectable
	 */
	protected $dataSource;

	/**
	 * @var string
	 */
	protected $primaryKey;

	/**
	 * @var Criteria
	 */
	protected $criteria;


	public function __construct(Collection $collection, string $primaryKey)
	{
		if (!($collection instanceof Selectable)) {
			throw new \LogicException(sprintf('Given collection must implement Selectable'));
		}

		$this->criteria = Criteria::create();
		$this->dataSource = $collection;
		$this->primaryKey = $primaryKey;
	}


	// *******************************************************************************
	// *                          IDataSource implementation                         *
	// *******************************************************************************


	public function getCount(): int
	{
		return $this->dataSource->matching($this->criteria)->count();
	}


	/**
	 * {@inheritDoc}
	 */
	public function getData(): array
	{
		return $this->dataSource->matching($this->criteria)->toArray();
	}


	/**
	 * {@inheritDoc}
	 */
	public function filterOne(array $condition): IDataSource
	{
		foreach ($condition as $column => $value) {
			if ($column === $this->primaryKey && is_numeric($value)) {
				$value = (int) $value;
			}

			$expr = Criteria::expr()->eq($column, $value);
			$this->criteria->andWhere($expr);
		}

		return $this;
	}


	public function limit(int $offset, int $limit): IDataSource
	{
		$this->criteria->setFirstResult($offset)->setMaxResults($limit);

		return $this;
	}


	public function sort(Sorting $sorting): IDataSource
	{
		if (is_callable($sorting->getSortCallback())) {
			call_user_func(
				$sorting->getSortCallback(),
				$this->criteria,
				$sorting->getSort()
			);

			return $this;
		}

		if ($sorting->getSort() !== []) {
			$this->criteria->orderBy($sorting->getSort());

			return $this;
		}

		$this->criteria->orderBy([$this->primaryKey => 'ASC']);

		return $this;
	}


	public function processAggregation(IAggregationFunction $function): void
	{
		$function->processDataSource(clone $this->dataSource);
	}


	/**
	 * {@inheritDoc}
	 */
	public function getDataSource()
	{
		return $this->dataSource;
	}


	protected function applyFilterDate(FilterDate $filter): void
	{
		foreach ($filter->getCondition() as $value) {
			try {
				$date = DateTimeHelper::tryConvertToDateTime($value, [$filter->getPhpFormat()]);

				$from = Criteria::expr()->gte($filter->getColumn(), $date->format('Y-m-d 00:00:00'));
				$to = Criteria::expr()->lte($filter->getColumn(), $date->format('Y-m-d 23:59:59'));

				$this->criteria->andWhere($from)->andWhere($to);
			} catch (DataGridDateTimeHelperException $ex) {
				// ignore the invalid filter value
			}
		}
	}


	protected function applyFilterDateRange(FilterDateRange $filter): void
	{
		$conditions = $filter->getCondition();
		$values = $conditions[$filter->getColumn()];

		$valueFrom = $values['from'];

		if ((bool) $valueFrom) {
			try {
				$dateFrom = DateTimeHelper::tryConvertToDateTime($valueFrom, [$filter->getPhpFormat()]);
				$dateFrom->setTime(0, 0, 0);

				$expr = Criteria::expr()->gte($filter->getColumn(), $dateFrom->format('Y-m-d H:i:s'));
				$this->criteria->andWhere($expr);
			} catch (DataGridDateTimeHelperException $ex) {
				// ignore the invalid filter value
			}
		}

		$valueTo = $values['to'];

		if ((bool) $valueTo) {
			try {
				$dateTo = DateTimeHelper::tryConvertToDateTime($valueTo, [$filter->getPhpFormat()]);
				$dateTo->setTime(23, 59, 59);

				$expr = Criteria::expr()->lte($filter->getColumn(), $dateTo->format('Y-m-d H:i:s'));
				$this->criteria->andWhere($expr);
			} catch (DataGridDateTimeHelperException $ex) {
				// ignore the invalid filter value
			}
		}
	}


	protected function applyFilterRange(FilterRange $filter): void
	{
		$conditions = $filter->getCondition();

		$valueFrom = $conditions[$filter->getColumn()]['from'];
		$valueTo = $conditions[$filter->getColumn()]['to'];

		if (is_numeric($valueFrom)) {
			$expr = Criteria::expr()->gte($filter->getColumn(), $valueFrom);
			$this->criteria->andWhere($expr);
		}

		if (is_numeric($valueTo)) {
			$expr = Criteria::expr()->lte($filter->getColumn(), $valueTo);
			$this->criteria->andWhere($expr);
		}
	}


	protected function applyFilterText(FilterText $filter): void
	{
		$exprs = [];

		foreach ($filter->getCondition() as $column => $value) {
			if ($filter->isExactSearch()) {
				$exprs[] = Criteria::expr()->eq($column, $value);

				continue;
			}

			$words = $filter->hasSplitWordsSearch() === false ? [$value] : explode(' ', $value);

			foreach ($words as $word) {
				$exprs[] = Criteria::expr()->contains($column, $word);
			}
		}

		$expr = call_user_func_array([Criteria::expr(), 'orX'], $exprs);
		$this->criteria->andWhere($expr);
	}


	protected function applyFilterMultiSelect(FilterMultiSelect $filter): void
	{
		$values = $filter->getCondition()[$filter->getColumn()];

		$expr = Criteria::expr()->in($filter->getColumn(), $values);
		$this->criteria->andWhere($expr);
	}


	protected function applyFilterSelect(FilterSelect $filter): void
	{
		foreach ($filter->getCondition() as $column => $value) {
			$expr = Criteria::expr()->eq($column, $value);
			$this->criteria->andWhere($expr);
		}
	}

}
