<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\DataSource;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Nette\SmartObject;
use Nette\Utils\Strings;
use Ublaboo\DataGrid\AggregationFunction\IAggregatable;
use Ublaboo\DataGrid\AggregationFunction\IAggregationFunction;
use Ublaboo\DataGrid\Exception\DataGridDateTimeHelperException;
use Ublaboo\DataGrid\Exception\DataGridException;
use Ublaboo\DataGrid\Filter\FilterDate;
use Ublaboo\DataGrid\Filter\FilterDateRange;
use Ublaboo\DataGrid\Filter\FilterMultiSelect;
use Ublaboo\DataGrid\Filter\FilterRange;
use Ublaboo\DataGrid\Filter\FilterSelect;
use Ublaboo\DataGrid\Filter\FilterText;
use Ublaboo\DataGrid\Utils\DateTimeHelper;
use Ublaboo\DataGrid\Utils\Sorting;

/**
 * @method void onDataLoaded(array $result)
 */
class DoctrineDataSource extends FilterableDataSource implements IDataSource, IAggregatable
{

	use SmartObject;

	/**
	 * Event called when datagrid data is loaded.
	 *
	 * @var array|callable[]
	 */
	public $onDataLoaded;

	/**
	 * @var QueryBuilder
	 */
	protected $dataSource;

	/**
	 * @var string
	 */
	protected $primaryKey;

	/**
	 * @var string|null
	 */
	protected $rootAlias;

	/**
	 * @var int
	 */
	protected $placeholder;

	/**
	 * @var array<string, mixed>
	 */
	protected $hints = [];


	public function __construct(QueryBuilder $dataSource, string $primaryKey)
	{
		$this->placeholder = count($dataSource->getParameters());
		$this->dataSource = $dataSource;
		$this->primaryKey = $primaryKey;
	}


	/**
	 * @param mixed $value
	 */
	public function setQueryHint(string $name, $value): IDataSource
	{
		$this->hints[$name] = $value;

		return $this;
	}


	public function getQuery(): Query
	{
		$query = $this->dataSource->getQuery();

		foreach ($this->hints as $name => $value) {
			$query->setHint($name, $value);
		}

		return $query;
	}


	// *******************************************************************************
	// *                          IDataSource implementation                         *
	// *******************************************************************************


	public function getCount(): int
	{
		if ($this->usePaginator()) {
			return (new Paginator($this->getQuery()))->count();
		}

		$dataSource = clone $this->dataSource;
		$dataSource->select(sprintf('COUNT(%s)', $this->checkAliases($this->primaryKey)));
		$dataSource->resetDQLPart('orderBy');

		return (int) $dataSource->getQuery()->getSingleScalarResult();
	}


	/**
	 * {@inheritDoc}
	 */
	public function getData(): array
	{
		if ($this->usePaginator()) {
			$iterator = (new Paginator($this->getQuery()))->getIterator();

			$data = iterator_to_array($iterator);
		} else {
			$data = $this->getQuery()->getResult();
		}

		$this->onDataLoaded($data);

		return $data;
	}


	/**
	 * {@inheritDoc}
	 */
	public function filterOne(array $condition): IDataSource
	{
		$p = $this->getPlaceholder();

		foreach ($condition as $column => $value) {
			$c = $this->checkAliases($column);

			$this->dataSource->andWhere("$c = :$p")
				->setParameter($p, $value);
		}

		return $this;
	}


	public function limit(int $offset, int $limit): IDataSource
	{
		$this->dataSource->setFirstResult($offset)->setMaxResults($limit);

		return $this;
	}


	public function sort(Sorting $sorting): IDataSource
	{
		if (is_callable($sorting->getSortCallback())) {
			call_user_func(
				$sorting->getSortCallback(),
				$this->dataSource,
				$sorting->getSort()
			);

			return $this;
		}

		$sort = $sorting->getSort();

		if ($sort !== []) {
			foreach ($sort as $column => $order) {
				$this->dataSource->addOrderBy($this->checkAliases((string) $column), $order);
			}
		} else {
			/**
			 * Has the statement already a order by clause?
			 */
			if (! (bool) $this->dataSource->getDQLPart('orderBy')) {
				$this->dataSource->orderBy($this->checkAliases($this->primaryKey));
			}
		}

		return $this;
	}


	/**
	 * Get unique int value for each instance class (self)
	 */
	public function getPlaceholder(): string
	{
		$return = 'param' . (string) ($this->placeholder + 1);

		$this->placeholder++;

		return $return;
	}


	public function processAggregation(IAggregationFunction $function): void
	{
		$function->processDataSource(clone $this->dataSource);
	}


	protected function applyFilterDate(FilterDate $filter): void
	{
		$p1 = $this->getPlaceholder();
		$p2 = $this->getPlaceholder();

		foreach ($filter->getCondition() as $column => $value) {
			try {
				$date = DateTimeHelper::tryConvertToDateTime($value, [$filter->getPhpFormat()]);
				$c = $this->checkAliases($column);

				$this->dataSource->andWhere("$c >= :$p1 AND $c <= :$p2")
					->setParameter($p1, $date->format('Y-m-d 00:00:00'))
					->setParameter($p2, $date->format('Y-m-d 23:59:59'));
			} catch (DataGridDateTimeHelperException $ex) {
				// ignore the invalid filter value
			}
		}
	}


	protected function applyFilterDateRange(FilterDateRange $filter): void
	{
		$conditions = $filter->getCondition();
		$c = $this->checkAliases($filter->getColumn());

		$valueFrom = $conditions[$filter->getColumn()]['from'];
		$valueTo = $conditions[$filter->getColumn()]['to'];

		if ($valueFrom) {
			try {
				$dateFrom = DateTimeHelper::tryConvertToDate($valueFrom, [$filter->getPhpFormat()]);
				$dateFrom->setTime(0, 0, 0);

				$p = $this->getPlaceholder();

				$this->dataSource->andWhere("$c >= :$p")->setParameter(
					$p,
					$dateFrom->format('Y-m-d H:i:s')
				);
			} catch (DataGridDateTimeHelperException $ex) {
				// ignore the invalid filter value
			}
		}

		if ($valueTo) {
			try {
				$dateTo = DateTimeHelper::tryConvertToDate($valueTo, [$filter->getPhpFormat()]);
				$dateTo->setTime(23, 59, 59);

				$p = $this->getPlaceholder();

				$this->dataSource->andWhere("$c <= :$p")->setParameter(
					$p,
					$dateTo->format('Y-m-d H:i:s')
				);
			} catch (DataGridDateTimeHelperException $ex) {
				// ignore the invalid filter value
			}
		}
	}


	protected function applyFilterRange(FilterRange $filter): void
	{
		$conditions = $filter->getCondition();
		$c = $this->checkAliases($filter->getColumn());

		$valueFrom = $conditions[$filter->getColumn()]['from'];
		$valueTo = $conditions[$filter->getColumn()]['to'];

		if (is_numeric($valueFrom)) {
			$p = $this->getPlaceholder();
			$this->dataSource->andWhere("$c >= :$p")->setParameter($p, $valueFrom);
		}

		if (is_numeric($valueTo)) {
			$p = $this->getPlaceholder();
			$this->dataSource->andWhere("$c <= :$p")->setParameter($p, $valueTo);
		}
	}


	protected function applyFilterText(FilterText $filter): void
	{
		$condition = $filter->getCondition();
		$exprs = [];

		foreach ($condition as $column => $value) {
			$c = $this->checkAliases($column);

			if ($filter->isExactSearch()) {
				$exprs[] = $this->dataSource->expr()->eq(
					$c,
					$this->dataSource->expr()->literal($value)
				);

				continue;
			}

			$words = $filter->hasSplitWordsSearch() === false ? [$value] : explode(' ', $value);

			foreach ($words as $word) {
				$exprs[] = $this->dataSource->expr()->like(
					$this->dataSource->expr()->lower($c),
					$this->dataSource->expr()->lower(
						$this->dataSource->expr()->literal("%$word%")
					)
				);
			}
		}

		$or = call_user_func_array([$this->dataSource->expr(), 'orX'], $exprs);

		$this->dataSource->andWhere($or);
	}


	protected function applyFilterMultiSelect(FilterMultiSelect $filter): void
	{
		$c = $this->checkAliases($filter->getColumn());
		$p = $this->getPlaceholder();

		$values = $filter->getCondition()[$filter->getColumn()];
		$expr = $this->dataSource->expr()->in($c, ':' . $p);

		$this->dataSource->andWhere($expr)->setParameter($p, $values);
	}


	protected function applyFilterSelect(FilterSelect $filter): void
	{
		$p = $this->getPlaceholder();

		foreach ($filter->getCondition() as $column => $value) {
			$c = $this->checkAliases($column);

			$this->dataSource->andWhere("$c = :$p")
				->setParameter($p, $value);
		}
	}


	/**
	 * {@inheritDoc}
	 */
	protected function getDataSource()
	{
		return $this->dataSource;
	}


	private function checkAliases(string $column): string
	{
		if (Strings::contains($column, '.')) {
			return $column;
		}

		if (!isset($this->rootAlias)) {
			$rootAlias = $this->dataSource->getRootAliases();

			if ($rootAlias === []) {
				throw new DataGridException('No root alias given from datasource');
			}

			$this->rootAlias = current($rootAlias);
		}

		return $this->rootAlias . '.' . $column;
	}


	private function usePaginator(): bool
	{
		$hasJoin = (bool) $this->dataSource->getDQLPart('join');
		$hasGroupBy = (bool) $this->dataSource->getDQLPart('groupBy');

		return $hasJoin || $hasGroupBy;
	}
}
