<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\DataSource;

use Nette\Utils\Strings;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\Mapper\Dbal\DbalCollection;
use Ublaboo\DataGrid\Filter\FilterDate;
use Ublaboo\DataGrid\Filter\FilterDateRange;
use Ublaboo\DataGrid\Filter\FilterMultiSelect;
use Ublaboo\DataGrid\Filter\FilterRange;
use Ublaboo\DataGrid\Filter\FilterSelect;
use Ublaboo\DataGrid\Filter\FilterText;
use Ublaboo\DataGrid\Utils\ArraysHelper;
use Ublaboo\DataGrid\Utils\DateTimeHelper;
use Ublaboo\DataGrid\Utils\Sorting;
use UnexpectedValueException;

class NextrasDataSource extends FilterableDataSource implements IDataSource
{

	/** @var ICollection */
	protected $dataSource;

	/** @var array */
	protected $data = [];

	/** @var string */
	protected $primaryKey;

	public function __construct(ICollection $dataSource, string $primaryKey)
	{
		$this->dataSource = $dataSource;
		$this->primaryKey = $primaryKey;
	}


	/********************************************************************************
	 *                          IDataSource implementation                          *
	 ********************************************************************************/

	/**
	 * {@inheritDoc}
	 */
	public function getCount(): int
	{
		return $this->dataSource->countStored();
	}


	/**
	 * {@inheritDoc}
	 */
	public function getData(): array
	{
		/**
		 * Paginator is better if the query uses ManyToMany associations
		 */
		return $this->data ?: $this->dataSource->fetchAll();
	}


	/**
	 * {@inheritDoc}
	 */
	public function filterOne(array $condition): IDataSource
	{
		$cond = [];

		foreach ($condition as $key => $value) {
			$cond[$this->prepareColumn($key)] = $value;
		}

		$this->dataSource = $this->dataSource->findBy($cond);

		return $this;
	}


	/**
	 * {@inheritDoc}
	 */
	public function limit(int $offset, int $limit): IDataSource
	{
		$this->dataSource = $this->dataSource->limitBy($limit, $offset);

		return $this;
	}


	/**
	 * {@inheritDoc}
	 */
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

		if (!empty($sort)) {
			foreach ($sort as $column => $order) {
				$this->dataSource = $this->dataSource->orderBy(
					$this->prepareColumn((string) $column),
					$order
				);
			}
		} else {
			if (!$this->dataSource instanceof DbalCollection) {
				throw new UnexpectedValueException(
					sprintf(
						'Expeting %s, got %s',
						DbalCollection::class,
						get_class($this->dataSource)
					)
				);
			}

			/**
			 * Has the statement already a order by clause?
			 */
			$order = $this->dataSource->getQueryBuilder()->getClause('order');

			if (ArraysHelper::testEmpty($order)) {
				$this->dataSource = $this->dataSource->orderBy(
					$this->prepareColumn($this->primaryKey)
				);
			}
		}

		return $this;
	}


	/**
	 * {@inheritDoc}
	 */
	protected function applyFilterDate(FilterDate $filter): void
	{
		foreach ($filter->getCondition() as $column => $value) {
			$date = DateTimeHelper::tryConvertToDateTime($value, [$filter->getPhpFormat()]);
			$date_end = clone $date;

			$this->dataSource = $this->dataSource->findBy([
				$this->prepareColumn($column) . '>=' => $date->setTime(0, 0, 0),
				$this->prepareColumn($column) . '<=' => $date_end->setTime(23, 59, 59),
			]);
		}
	}


	/**
	 * {@inheritDoc}
	 */
	protected function applyFilterDateRange(FilterDateRange $filter): void
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
			$this->dataSource = $this->dataSource->findBy($dataCondition);
		}
	}


	/**
	 * {@inheritDoc}
	 */
	protected function applyFilterRange(FilterRange $filter): void
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
			$this->dataSource = $this->dataSource->findBy($dataCondition);
		}
	}


	/**
	 * {@inheritDoc}
	 */
	protected function applyFilterText(FilterText $filter): void
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

			$words = $filter->hasSplitWordsSearch() === false ? [$value] : explode(' ', $value);

			foreach ($words as $word) {
				$expr .= '%column LIKE %s OR ';
				$params[] = $column;
				$params[] = "%$word%";
			}
		}

		$expr = preg_replace('/ OR $/', ')', $expr);

		array_unshift($params, $expr);

		if (!$this->dataSource instanceof DbalCollection) {
			throw new UnexpectedValueException(
				sprintf(
					'Expeting %s, got %s',
					DbalCollection::class,
					get_class($this->dataSource)
				)
			);
		}

		$this->dataSource->getQueryBuilder()->andWhere(...$params);
	}


	/**
	 * {@inheritDoc}
	 */
	protected function applyFilterMultiSelect(FilterMultiSelect $filter): void
	{
		$this->applyFilterSelect($filter);
	}


	/**
	 * {@inheritDoc}
	 */
	protected function applyFilterSelect(FilterSelect $filter): void
	{
		$this->dataSource = $this->dataSource->findBy(
			[$this->prepareColumn($filter->getColumn()) => $filter->getValue()]
		);
	}


	/**
	 * {@inheritDoc}
	 */
	protected function getDataSource()
	{
		return $this->dataSource;
	}


	/**
	 * Adjust column from DataGrid 'foreignKey.column' to Nextras 'this->foreignKey->column'
	 */
	private function prepareColumn(string $column): string
	{
		if (Strings::contains($column, '.')) {
			return 'this->' . str_replace('.', '->', $column);
		}

		return $column;
	}

}
