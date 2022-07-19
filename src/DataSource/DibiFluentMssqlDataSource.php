<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\DataSource;

use Dibi\Fluent;
use Dibi\Helpers;
use Dibi\Result;
use Ublaboo\DataGrid\Exception\DataGridDateTimeHelperException;
use Ublaboo\DataGrid\Filter\FilterDate;
use Ublaboo\DataGrid\Filter\FilterDateRange;
use Ublaboo\DataGrid\Filter\FilterText;
use Ublaboo\DataGrid\Utils\DateTimeHelper;
use UnexpectedValueException;

class DibiFluentMssqlDataSource extends DibiFluentDataSource
{

	/**
	 * @var array
	 */
	protected $data = [];


	public function __construct(Fluent $dataSource, string $primaryKey)
	{
		parent::__construct($dataSource, $primaryKey);
	}


	// *******************************************************************************
	// *                          IDataSource implementation                         *
	// *******************************************************************************


	public function getCount(): int
	{
		$clone = clone $this->dataSource;
		$clone->removeClause('ORDER BY');

		return $clone->count();
	}


	/**
	 * {@inheritDoc}
	 */
	public function filterOne(array $condition): IDataSource
	{
		$this->dataSource->where($condition);

		return $this;
	}


	public function limit(int $offset, int $limit): IDataSource
	{
		$sql = (string) $this->dataSource;

		$result = $this->dataSource->getConnection()
			->query('%sql OFFSET ? ROWS FETCH NEXT ? ROWS ONLY', $sql, $offset, $limit);

		if (!$result instanceof Result) {
			throw new UnexpectedValueException();
		}

		$this->data = $result->fetchAll();

		return $this;
	}


	protected function applyFilterDate(FilterDate $filter): void
	{
		$conditions = $filter->getCondition();

		try {
			$date = DateTimeHelper::tryConvertToDateTime(
				$conditions[$filter->getColumn()],
				[$filter->getPhpFormat()]
			);

			$this->dataSource->where(
				'CONVERT(varchar(10), %n, 112) = ?',
				$filter->getColumn(),
				$date->format('Ymd')
			);
		} catch (DataGridDateTimeHelperException $ex) {
			// ignore the invalid filter value
		}
	}


	protected function applyFilterDateRange(FilterDateRange $filter): void
	{
		$conditions = $filter->getCondition();

		$valueFrom = $conditions[$filter->getColumn()]['from'];
		$valueTo = $conditions[$filter->getColumn()]['to'];

		if ($valueFrom) {
			$this->dataSource->where(
				'CONVERT(varchar(10), %n, 112) >= ?',
				$filter->getColumn(),
				$valueFrom
			);
		}

		if ($valueTo) {
			$this->dataSource->where(
				'CONVERT(varchar(10), %n, 112) <= ?',
				$filter->getColumn(),
				$valueTo
			);
		}
	}


	protected function applyFilterText(FilterText $filter): void
	{
		$condition = $filter->getCondition();
		$driver = $this->dataSource->getConnection()->getDriver();
		$or = [];

		foreach ($condition as $column => $value) {
			$column = Helpers::escape($driver, $column, \dibi::IDENTIFIER);

			if ($filter->isExactSearch()) {
				$this->dataSource->where("$column = %s", $value);

				continue;
			}

			$or[] = "$column LIKE \"%$value%\"";
		}

		if (sizeof($or) > 1) {
			$this->dataSource->where('(%or)', $or);
		} else {
			$this->dataSource->where($or);
		}
	}
}
