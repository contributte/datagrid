<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\DataSource;

use Ublaboo\DataGrid\Filter;
use Ublaboo\DataGrid\Utils\DateTimeHelper;

class NetteDatabaseTableMssqlDataSource extends NetteDatabaseTableDataSource implements IDataSource
{

	/**
	 * Filter by date
	 * @param  Filter\FilterDate $filter
	 * @return void
	 */
	public function applyFilterDate(Filter\FilterDate $filter)
	{
		$conditions = $filter->getCondition();

		$date = DateTimeHelper::tryConvertToDateTime($conditions[$filter->getColumn()], [$filter->getPhpFormat()]);

		$this->data_source->where(
			"CONVERT(varchar(10), {$filter->getColumn()}, 112) = ?",
			$date->format('Ymd')
		);
	}


	/**
	 * Filter by date range
	 * @param  Filter\FilterDateRange $filter
	 * @return void
	 */
	public function applyFilterDateRange(Filter\FilterDateRange $filter)
	{
		$conditions = $filter->getCondition();

		$value_from = $conditions[$filter->getColumn()]['from'];
		$value_to = $conditions[$filter->getColumn()]['to'];

		if ($value_from) {
			$date_from = DateTimeHelper::tryConvertToDateTime($value_from, [$filter->getPhpFormat()]);
			$date_from->setTime(0, 0, 0);

			$this->data_source->where(
				"CONVERT(varchar(10), {$filter->getColumn()}, 112) >= ?",
				$date_from->format('Ymd')
			);
		}

		if ($value_to) {
			$date_to = DateTimeHelper::tryConvertToDateTime($value_to, [$filter->getPhpFormat()]);
			$date_to->setTime(23, 59, 59);

			$this->data_source->where(
				"CONVERT(varchar(10), {$filter->getColumn()}, 112) <= ?",
				$date_to->format('Ymd')
			);
		}
	}
}
