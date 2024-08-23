<?php declare(strict_types = 1);

namespace Contributte\Datagrid\DataSource;

use Contributte\Datagrid\AggregationFunction\IAggregationFunction;
use Contributte\Datagrid\Exception\DatagridDateTimeHelperException;
use Contributte\Datagrid\Filter\FilterDate;
use Contributte\Datagrid\Filter\FilterDateRange;
use Contributte\Datagrid\Filter\FilterMultiSelect;
use Contributte\Datagrid\Filter\FilterRange;
use Contributte\Datagrid\Filter\FilterSelect;
use Contributte\Datagrid\Filter\FilterText;
use Contributte\Datagrid\Utils\DateTimeHelper;
use Contributte\Datagrid\Utils\Sorting;
use LogicException;
use Nette\Database\Table\Selection;
use Nette\Utils\Strings;

class NetteDatabaseTableDataSource extends FilterableDataSource implements IDataSource
{

	protected array $data = [];

	public function __construct(protected Selection $dataSource, protected string $primaryKey)
	{
	}

	public function getCount(): int
	{
		$dataSourceSqlBuilder = $this->dataSource->getSqlBuilder();

		try {
			$primary = $this->dataSource->getPrimary();

		} catch (LogicException) {
			if ($dataSourceSqlBuilder->getGroup() !== '') {
				return $this->dataSource->count(
					'DISTINCT ' . Strings::replace($dataSourceSqlBuilder->getGroup(), '~ (DESC|ASC)~')
				);
			}

			return $this->dataSource->count('*');
		}

		if ($dataSourceSqlBuilder->getGroup() !== '') {
			return $this->dataSource->count(
				'DISTINCT ' . Strings::replace($dataSourceSqlBuilder->getGroup(), '~ (DESC|ASC)~')
			);
		}

		return $this->dataSource->count(
			$this->dataSource->getName() . '.' . (is_array($primary) ? reset($primary) : $primary)
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getData(): array
	{
		return $this->data !== []
			? $this->data
			: $this->dataSource->fetchAll();
	}

	/**
	 * {@inheritDoc}
	 */
	public function filterOne(array $condition): IDataSource
	{
		$this->dataSource->where($condition)->limit(1);

		return $this;
	}

	/**
	 * @phpstan-param positive-int|0 $offset
	 * @phpstan-param positive-int|0 $limit
	 */
	public function limit(int $offset, int $limit): IDataSource
	{
		$this->data = $this->dataSource->limit($limit, $offset)->fetchAll();

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
			$this->dataSource->getSqlBuilder()->setOrder([], []);

			foreach ($sort as $column => $order) {
				$this->dataSource->order(sprintf('%s %s', $column, $order));
			}
		} else {
			/**
			 * Has the statement already a order by clause?
			 */
			if ($this->dataSource->getSqlBuilder()->getOrder() === []) {
				$this->dataSource->order($this->primaryKey);
			}
		}

		return $this;
	}

	public function processAggregation(IAggregationFunction $function): void
	{
		$function->processDataSource(clone $this->dataSource);
	}

	protected function applyFilterDate(FilterDate $filter): void
	{
		$conditions = $filter->getCondition();

		try {
			$date = DateTimeHelper::tryConvertToDateTime($conditions[$filter->getColumn()], [$filter->getPhpFormat()]);

			$this->dataSource->where(sprintf('DATE(%s) = ?', $filter->getColumn()), $date->format('Y-m-d'));
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

				$this->dataSource->where(
					sprintf('DATE(%s) >= ?', $filter->getColumn()),
					$dateFrom->format('Y-m-d')
				);
			} catch (DatagridDateTimeHelperException) {
				// ignore the invalid filter value
			}
		}

		if ($valueTo) {
			try {
				$dateTo = DateTimeHelper::tryConvertToDateTime($valueTo, [$filter->getPhpFormat()]);
				$dateTo->setTime(23, 59, 59);

				$this->dataSource->where(
					sprintf('DATE(%s) <= ?', $filter->getColumn()),
					$dateTo->format('Y-m-d')
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
			$this->dataSource->where(sprintf('%s >= ?', $filter->getColumn()), $valueFrom);
		}

		if ($valueTo !== '') {
			$this->dataSource->where(sprintf('%s <= ?', $filter->getColumn()), $valueTo);
		}
	}

	protected function applyFilterText(FilterText $filter): void
	{
		$or = [];
		$args = [];
		$bigOr = '(';
		$bigOrArgs = [];
		$condition = $filter->getCondition();

		foreach ($condition as $column => $value) {
			$like = '(';
			$args = [];

			if ($filter->isExactSearch()) {
				$like .= sprintf('%s = ? OR ', $column);
				$args[] = sprintf('%s', $value);
			} else {
				$words = $filter->hasSplitWordsSearch() === false ? [$value] : explode(' ', $value);

				foreach ($words as $word) {
					$like .= sprintf('%s LIKE ? OR ', $column);
					$args[] = sprintf('%%%s%%', $word);
				}
			}

			$like = substr($like, 0, strlen($like) - 4) . ')';

			$or[] = $like;
			$bigOr .= sprintf('%s OR ', $like);
			$bigOrArgs = [...$bigOrArgs, ...$args];
		}

		if (count($or) > 1) {
			$bigOr = substr($bigOr, 0, strlen($bigOr) - 4) . ')';

			$query = [...[$bigOr], ...$bigOrArgs];

			call_user_func_array([$this->dataSource, 'where'], $query);
		} else {
			$query = [...$or, ...$args];

			call_user_func_array([$this->dataSource, 'where'], $query);
		}
	}

	protected function applyFilterMultiSelect(FilterMultiSelect $filter): void
	{
		$condition = $filter->getCondition();
		$values = $condition[$filter->getColumn()];
		$or = '(';

		if ((is_countable($values) ? count($values) : 0) > 1) {
			$length = is_countable($values) ? count($values) : 0;
			$i = 1;

			for ($iterator = 0; $iterator < (is_countable($values) ? count($values) : 0); $iterator++) {
				if ($i === $length) {
					$or .= $filter->getColumn() . ' = ?)';
				} else {
					$or .= $filter->getColumn() . ' = ? OR ';
				}

				$i++;
			}

			array_unshift($values, $or);

			call_user_func_array([$this->dataSource, 'where'], $values);
		} else {
			$this->dataSource->where($filter->getColumn() . ' = ?', reset($values));
		}
	}

	protected function applyFilterSelect(FilterSelect $filter): void
	{
		$this->dataSource->where($filter->getCondition());
	}

	protected function getDataSource(): Selection
	{
		return $this->dataSource;
	}

}
