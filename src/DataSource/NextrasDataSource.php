<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\DataSource;

use Nette\Utils\Strings;
use Nextras\Orm\Collection\Expression\LikeExpression;
use Nextras\Orm\Collection\ICollection;
use Ublaboo\DataGrid\AggregationFunction\IAggregatable;
use Ublaboo\DataGrid\AggregationFunction\IAggregationFunction;
use Ublaboo\DataGrid\Exception\DataGridDateTimeHelperException;
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

class NextrasDataSource extends FilterableDataSource implements IDataSource, IAggregatable
{

	/**
	 * @var ICollection
	 */
	protected $dataSource;

	/**
	 * @var array
	 */
	protected $data = [];

	/**
	 * @var string
	 */
	protected $primaryKey;

	/**
	 * @var string
	 */
	private $dbalCollectionClass;

	public function __construct(ICollection $dataSource, string $primaryKey)
	{
		$this->dataSource = $dataSource;
		$this->primaryKey = $primaryKey;
		// Support version 4.0 with 3.1 backward compatibility
		$this->dbalCollectionClass = class_exists('Nextras\Orm\Collection\DbalCollection')
			? 'Nextras\Orm\Collection\DbalCollection'
			: 'Nextras\Orm\Mapper\Dbal\DbalCollection';
	}


	// *******************************************************************************
	// *                          IDataSource implementation                         *
	// *******************************************************************************


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
		return $this->data !== []
			? $this->data
			: $this->dataSource->fetchAll();
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


	public function limit(int $offset, int $limit): IDataSource
	{
		$this->dataSource = $this->dataSource->limitBy($limit, $offset);

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
				$this->dataSource = $this->dataSource->orderBy(
					$this->prepareColumn((string) $column),
					$order
				);
			}
		} else {
			if (!$this->dataSource instanceof $this->dbalCollectionClass) {
				throw new UnexpectedValueException(
					sprintf(
						'Expeting %s, got %s',
						$this->dbalCollectionClass,
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

	public function processAggregation(IAggregationFunction $function): void
	{
		$function->processDataSource( clone $this->dataSource );
	}


	protected function applyFilterDate(FilterDate $filter): void
	{
		foreach ($filter->getCondition() as $column => $value) {
			try {
				$date = DateTimeHelper::tryConvertToDateTime($value, [$filter->getPhpFormat()]);
				$date_end = clone $date;

				$this->dataSource = $this->dataSource->findBy([
					$this->prepareColumn($column) . '>=' => $date->setTime(0, 0, 0),
					$this->prepareColumn($column) . '<=' => $date_end->setTime(23, 59, 59),
				]);
			} catch (DataGridDateTimeHelperException $ex) {
				// ignore the invalid filter value
			}
		}
	}


	protected function applyFilterDateRange(FilterDateRange $filter): void
	{
		$conditions = $filter->getCondition();

		$valueFrom = $conditions[$filter->getColumn()]['from'];
		$valueTo = $conditions[$filter->getColumn()]['to'];

		$dataCondition = [];

		if ($valueFrom) {
			try {
				$dateFrom = DateTimeHelper::tryConvertToDateTime(
					$valueFrom,
					[$filter->getPhpFormat()]
				);
				$dataCondition[$this->prepareColumn($filter->getColumn()) . '>='] = $dateFrom->setTime(0, 0, 0);
			} catch (DataGridDateTimeHelperException $ex) {
				// ignore the invalid filter value
			}
		}

		if ($valueTo) {
			try {
				$dateTo = DateTimeHelper::tryConvertToDateTime(
					$valueTo,
					[$filter->getPhpFormat()]
				);
				$dataCondition[$this->prepareColumn($filter->getColumn()) . '<='] = $dateTo->setTime(23, 59, 59);
			} catch (DataGridDateTimeHelperException $ex) {
				// ignore the invalid filter value
			}
		}

		if ($dataCondition !== []) {
			$this->dataSource = $this->dataSource->findBy($dataCondition);
		}
	}


	protected function applyFilterRange(FilterRange $filter): void
	{
		$conditions = $filter->getCondition();

		$valueFrom = $conditions[$filter->getColumn()]['from'];
		$valueTo = $conditions[$filter->getColumn()]['to'];

		$dataCondition = [];

		if (is_numeric($valueFrom)) {
			$dataCondition[$this->prepareColumn($filter->getColumn()) . '>='] = $valueFrom;
		}

		if (is_numeric($valueTo)) {
			$dataCondition[$this->prepareColumn($filter->getColumn()) . '<='] = $valueTo;
		}

		if ($dataCondition !== []) {
			$this->dataSource = $this->dataSource->findBy($dataCondition);
		}
	}


	protected function applyFilterText(FilterText $filter): void
	{
		// native handling with LikeFunction in v4
		if (class_exists(LikeExpression::class)) {
			$conditions = [
				ICollection::OR,
			];

			foreach ($filter->getCondition() as $column => $value) {
				$preparedColumn = $this->prepareColumn($column);

				if ($filter->isExactSearch()) {
					$conditions[] = [
						$preparedColumn => $value,
					];
				} else {
					$words = $filter->hasSplitWordsSearch() === false ? [$value] : explode(' ', $value);

					foreach ($words as $word) {
						$conditions[] = [
							$preparedColumn . '~' => LikeExpression::contains($word),
						];
					}
				}
			}

			$this->dataSource = $this->dataSource->findBy($conditions);

			return;
		}

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

		if (!$this->dataSource instanceof $this->dbalCollectionClass) {
			throw new UnexpectedValueException(
				sprintf(
					'Expeting %s, got %s',
					$this->dbalCollectionClass,
					get_class($this->dataSource)
				)
			);
		}

		$this->dataSource->getQueryBuilder()->andWhere(...$params);
	}


	protected function applyFilterMultiSelect(FilterMultiSelect $filter): void
	{
		$this->applyFilterSelect($filter);
	}


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
			// 'this->' is deprecated in v4
			$prefix = $this->dbalCollectionClass === 'Nextras\Orm\Collection\DbalCollection'
				? ''
				: 'this->';

			return $prefix . str_replace('.', '->', $column);
		}

		return $column;
	}
}
