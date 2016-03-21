<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\DataSource;

use Ublaboo\DataGrid\Filter;
use Ublaboo\DataGrid\Utils\NetteDatabaseSelectionHelper;

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

		$date = \DateTime::createFromFormat($filter->getPhpFormat(), $conditions[$filter->getColumn()]);

		$this->data_source->where(
			"CONVERT(varchar(10), {$filter->getColumn()}, 112) = ?",
			$date->format('Y-m-d')
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
		$value_to   = $conditions[$filter->getColumn()]['to'];

		if ($value_from) {
			$date_from = \DateTime::createFromFormat($filter->getPhpFormat(), $value_from);
			$date_from->setTime(0, 0, 0);

			$this->data_source->where(
				"CONVERT(varchar(10), {$filter->getColumn()}, 112) >= ?",
				$date_from->format('Y-m-d')
			);
		}

		if ($value_to) {
			$date_to = \DateTime::createFromFormat($filter->getPhpFormat(), $value_to);
			$date_to->setTime(23, 59, 59);

			$this->data_source->where(
				"CONVERT(varchar(10), {$filter->getColumn()}, 112) <= ?",
				$date_to->format('Y-m-d')
			);
		}
	}


	/**
	 * Apply limit and offset on data
	 * @param int $offset
	 * @param int $limit
	 * @return static
	 */
	public function limit($offset, $limit)
	{
		$context = NetteDatabaseSelectionHelper::getContext($this->data_source);

		$sql = (string) $this->data_source->getSql();

		$params = $this->data_source->getSqlBuilder()->getParameters();

		$params[] = $offset;
		$params[] = $limit;

		array_unshift($params, "$sql OFFSET ? ROWS FETCH NEXT ? ROWS ONLY");

		$result = call_user_func_array([$context, 'query'], $params);

		$this->data = $result->fetchAll();

		return $this;
	}

}
