<?php declare(strict_types = 1);

namespace Contributte\Datagrid\DataSource;

use Contributte\Datagrid\Exception\DatagridDateTimeHelperException;
use Contributte\Datagrid\Filter\FilterDate;
use Contributte\Datagrid\Filter\FilterDateRange;
use Contributte\Datagrid\Filter\FilterMultiSelect;
use Contributte\Datagrid\Filter\FilterRange;
use Contributte\Datagrid\Filter\FilterSelect;
use Contributte\Datagrid\Filter\FilterText;
use Contributte\Datagrid\Utils\DateTimeHelper;
use Contributte\Datagrid\Utils\Sorting;
use Nette\Database\Explorer;
use Nette\Database\ResultSet;

class NetteDatabaseDataSource extends FilterableDataSource implements IDataSource
{

	protected array $data = [];

	/** @var mixed[] */
	protected array $queryParameters;

	/** @var array<array{string, mixed[]}> */
	protected array $whereConditions = [];

	protected ?string $orderByClause = null;

	/**
	 * @param mixed[] $params
	 */
	public function __construct(
		protected Explorer $connection,
		protected string $sql,
		array $params = [],
	)
	{
		$this->queryParameters = $params;
	}

	public function getCount(): int
	{
		$sql = sprintf('SELECT COUNT(*) AS count FROM (%s) AS datagrid_count', $this->buildFilteredSql());

		$row = $this->query($sql, $this->buildParams())->fetch();

		return $row !== null ? (int) $row['count'] : 0;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getData(): array
	{
		return $this->data !== []
			? $this->data
			: $this->query($this->buildFinalSql(), $this->buildParams())->fetchAll();
	}

	/**
	 * {@inheritDoc}
	 */
	public function filterOne(array $condition): IDataSource
	{
		foreach ($condition as $column => $value) {
			$this->addWhereCondition(sprintf('%s = ?', $column), [$value]);
		}

		return $this;
	}

	/**
	 * @phpstan-param positive-int|0 $offset
	 * @phpstan-param positive-int|0 $limit
	 */
	public function limit(int $offset, int $limit): IDataSource
	{
		$sql = sprintf('%s LIMIT %d OFFSET %d', $this->buildFinalSql(), $limit, $offset);

		$this->data = $this->query($sql, $this->buildParams())->fetchAll();

		return $this;
	}

	public function sort(Sorting $sorting): IDataSource
	{
		if (is_callable($sorting->getSortCallback())) {
			call_user_func(
				$sorting->getSortCallback(),
				$this->sql,
				$sorting->getSort()
			);

			return $this;
		}

		$sort = $sorting->getSort();

		if ($sort !== []) {
			$parts = [];

			foreach ($sort as $column => $order) {
				$parts[] = sprintf('%s %s', $column, $order);
			}

			$this->orderByClause = implode(', ', $parts);
		}

		return $this;
	}

	public function getDataSource(): Explorer
	{
		return $this->connection;
	}

	/**
	 * Returns the current SQL query and its parameters.
	 *
	 * @return array{string, mixed[]}
	 */
	public function getQuery(): array
	{
		return [$this->buildFinalSql(), $this->buildParams()];
	}

	/**
	 * @param mixed[] $params
	 */
	protected function addWhereCondition(string $sql, array $params = []): void
	{
		$this->whereConditions[] = [$sql, $params];
	}

	protected function buildFilteredSql(): string
	{
		if ($this->whereConditions === []) {
			return $this->sql;
		}

		$whereParts = array_map(static fn (array $c): string => $c[0], $this->whereConditions);
		$whereClause = implode(' AND ', $whereParts);

		return sprintf('SELECT * FROM (%s) AS datagrid_base WHERE %s', $this->sql, $whereClause);
	}

	protected function buildFinalSql(): string
	{
		$sql = $this->buildFilteredSql();

		if ($this->orderByClause !== null) {
			$sql .= sprintf(' ORDER BY %s', $this->orderByClause);
		}

		return $sql;
	}

	/**
	 * @return mixed[]
	 */
	protected function buildParams(): array
	{
		$params = $this->queryParameters;

		foreach ($this->whereConditions as [, $conditionParams]) {
			$params = array_merge($params, $conditionParams);
		}

		return $params;
	}

	/**
	 * @param mixed[] $params
	 * @return ResultSet<mixed>
	 */
	protected function query(string $sql, array $params = []): ResultSet
	{
		/** @phpstan-ignore argument.type */
		return $this->connection->query($sql, ...$params);
	}

	protected function applyFilterDate(FilterDate $filter): void
	{
		$conditions = $filter->getCondition();

		try {
			$date = DateTimeHelper::tryConvertToDateTime($conditions[$filter->getColumn()], [$filter->getPhpFormat()]);

			$this->addWhereCondition(
				sprintf('DATE(%s) = ?', $filter->getColumn()),
				[$date->format('Y-m-d')]
			);
		} catch (DatagridDateTimeHelperException) {
			// ignore the invalid filter value
		}
	}

	protected function applyFilterDateRange(FilterDateRange $filter): void
	{
		$conditions = $filter->getCondition();

		$valueFrom = $conditions[$filter->getColumn()]['from'];
		$valueTo = $conditions[$filter->getColumn()]['to'];

		if ($valueFrom) {
			try {
				$dateFrom = DateTimeHelper::tryConvertToDateTime($valueFrom, [$filter->getPhpFormat()]);
				$dateFrom->setTime(0, 0, 0);

				$this->addWhereCondition(
					sprintf('DATE(%s) >= ?', $filter->getColumn()),
					[$dateFrom->format('Y-m-d')]
				);
			} catch (DatagridDateTimeHelperException) {
				// ignore the invalid filter value
			}
		}

		if ($valueTo) {
			try {
				$dateTo = DateTimeHelper::tryConvertToDateTime($valueTo, [$filter->getPhpFormat()]);
				$dateTo->setTime(23, 59, 59);

				$this->addWhereCondition(
					sprintf('DATE(%s) <= ?', $filter->getColumn()),
					[$dateTo->format('Y-m-d')]
				);
			} catch (DatagridDateTimeHelperException) {
				// ignore the invalid filter value
			}
		}
	}

	protected function applyFilterRange(FilterRange $filter): void
	{
		$conditions = $filter->getCondition();

		$valueFrom = $conditions[$filter->getColumn()]['from'];
		$valueTo = $conditions[$filter->getColumn()]['to'];

		if ($valueFrom !== '') {
			$this->addWhereCondition(
				sprintf('%s >= ?', $filter->getColumn()),
				[$valueFrom]
			);
		}

		if ($valueTo !== '') {
			$this->addWhereCondition(
				sprintf('%s <= ?', $filter->getColumn()),
				[$valueTo]
			);
		}
	}

	protected function applyFilterText(FilterText $filter): void
	{
		$condition = $filter->getCondition();
		$operator = $filter->hasConjunctionSearch() ? 'AND' : 'OR';
		$or = [];
		$bigOrArgs = [];

		foreach ($condition as $column => $value) {
			$like = '(';
			$args = [];

			if ($filter->isExactSearch()) {
				$like .= sprintf('%s = ? %s ', $column, $operator);
				$args[] = $value;
			} else {
				$words = $filter->hasSplitWordsSearch() === false ? [$value] : explode(' ', $value);

				foreach ($words as $word) {
					$like .= sprintf('%s LIKE ? %s ', $column, $operator);
					$args[] = sprintf('%%%s%%', $word);
				}
			}

			$like = substr($like, 0, strlen($like) - (strlen($operator) + 2)) . ')';

			$or[] = $like;
			$bigOrArgs = array_merge($bigOrArgs, $args);
		}

		if (count($or) > 1) {
			$bigOr = '(' . implode(sprintf(' %s ', $operator), $or) . ')';
			$this->addWhereCondition($bigOr, $bigOrArgs);
		} else {
			$this->addWhereCondition((string) reset($or), $bigOrArgs);
		}
	}

	protected function applyFilterMultiSelect(FilterMultiSelect $filter): void
	{
		$condition = $filter->getCondition();
		$values = $condition[$filter->getColumn()];

		$placeholders = implode(', ', array_fill(0, count($values), '?'));
		$this->addWhereCondition(
			sprintf('%s IN (%s)', $filter->getColumn(), $placeholders),
			array_values($values)
		);
	}

	protected function applyFilterSelect(FilterSelect $filter): void
	{
		foreach ($filter->getCondition() as $column => $value) {
			$this->addWhereCondition(sprintf('%s = ?', $column), [$value]);
		}
	}

}
