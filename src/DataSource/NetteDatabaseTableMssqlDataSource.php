<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\DataSource;

use Ublaboo\DataGrid\Exception\DataGridDateTimeHelperException;
use Ublaboo\DataGrid\Filter\FilterDate;
use Ublaboo\DataGrid\Filter\FilterDateRange;
use Ublaboo\DataGrid\Utils\DateTimeHelper;

class NetteDatabaseTableMssqlDataSource extends NetteDatabaseTableDataSource implements IDataSource
{

	protected function applyFilterDate(FilterDate $filter): void
	{
		$conditions = $filter->getCondition();

		try {
			$date = DateTimeHelper::tryConvertToDateTime($conditions[$filter->getColumn()], [$filter->getPhpFormat()]);

			$this->dataSource->where(
				"CONVERT(varchar(10), {$filter->getColumn()}, 112) = ?",
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
			try {
				$dateFrom = DateTimeHelper::tryConvertToDateTime($valueFrom, [$filter->getPhpFormat()]);
				$dateFrom->setTime(0, 0, 0);

				$this->dataSource->where(
					"CONVERT(varchar(10), {$filter->getColumn()}, 112) >= ?",
					$dateFrom->format('Ymd')
				);
			} catch (DataGridDateTimeHelperException $ex) {
				// ignore the invalid filter value
			}
		}

		if ($valueTo) {
			try {
				$dateTo = DateTimeHelper::tryConvertToDateTime($valueTo, [$filter->getPhpFormat()]);
				$dateTo->setTime(23, 59, 59);

				$this->dataSource->where(
					"CONVERT(varchar(10), {$filter->getColumn()}, 112) <= ?",
					$dateTo->format('Ymd')
				);
			} catch (DataGridDateTimeHelperException $ex) {
				// ignore the invalid filter value
			}
		}
	}
}
