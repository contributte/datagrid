<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\DataSource;

use Ublaboo\DataGrid\Filter;
use Ublaboo\DataGrid\Filter\FilterDate;
use Ublaboo\DataGrid\Filter\FilterDateRange;
use Ublaboo\DataGrid\Utils\DateTimeHelper;

class NetteDatabaseTableMssqlDataSource extends NetteDatabaseTableDataSource implements IDataSource
{

	/**
	 * {@inheritDoc}
	 */
	protected function applyFilterDate(FilterDate $filter): void
	{
		$conditions = $filter->getCondition();

		$date = DateTimeHelper::tryConvertToDateTime($conditions[$filter->getColumn()], [$filter->getPhpFormat()]);

		$this->dataSource->where(
			"CONVERT(varchar(10), {$filter->getColumn()}, 112) = ?",
			$date->format('Ymd')
		);
	}


	/**
	 * {@inheritDoc}
	 */
	protected function applyFilterDateRange(FilterDateRange $filter): void
	{
		$conditions = $filter->getCondition();

		$valueFrom = $conditions[$filter->getColumn()]['from'];
		$valueTo = $conditions[$filter->getColumn()]['to'];

		if ($valueFrom) {
			$dateFrom = DateTimeHelper::tryConvertToDateTime($valueFrom, [$filter->getPhpFormat()]);
			$dateFrom->setTime(0, 0, 0);

			$this->dataSource->where(
				"CONVERT(varchar(10), {$filter->getColumn()}, 112) >= ?",
				$dateFrom->format('Ymd')
			);
		}

		if ($valueTo) {
			$dateTo = DateTimeHelper::tryConvertToDateTime($valueTo, [$filter->getPhpFormat()]);
			$dateTo->setTime(23, 59, 59);

			$this->dataSource->where(
				"CONVERT(varchar(10), {$filter->getColumn()}, 112) <= ?",
				$dateTo->format('Ymd')
			);
		}
	}
}
