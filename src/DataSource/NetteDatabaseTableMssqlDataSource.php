<?php declare(strict_types = 1);

namespace Contributte\Datagrid\DataSource;

use Contributte\Datagrid\Exception\DatagridDateTimeHelperException;
use Contributte\Datagrid\Filter\FilterDate;
use Contributte\Datagrid\Filter\FilterDateRange;
use Contributte\Datagrid\Utils\DateTimeHelper;

class NetteDatabaseTableMssqlDataSource extends NetteDatabaseTableDataSource implements IDataSource
{

	protected function applyFilterDate(FilterDate $filter): void
	{
		$conditions = $filter->getCondition();

		try {
			$date = DateTimeHelper::tryConvertToDateTime($conditions[$filter->getColumn()], [$filter->getPhpFormat()]);

			$this->dataSource->where(
				sprintf('CONVERT(varchar(10), %s, 112) = ?', $filter->getColumn()),
				$date->format('Ymd')
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

				$this->dataSource->where(
					sprintf('CONVERT(varchar(10), %s, 112) >= ?', $filter->getColumn()),
					$dateFrom->format('Ymd')
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
					sprintf('CONVERT(varchar(10), %s, 112) <= ?', $filter->getColumn()),
					$dateTo->format('Ymd')
				);
			} catch (DatagridDateTimeHelperException) {
				// ignore the invalid filter value
			}
		}
	}

}
