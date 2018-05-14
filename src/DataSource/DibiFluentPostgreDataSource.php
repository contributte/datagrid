<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\DataSource;

use Dibi;
use DibiFluent;
use Ublaboo\DataGrid\Filter;

class DibiFluentPostgreDataSource extends DibiFluentDataSource
{

	/**
	 * Filter by keyword
	 */
	public function applyFilterText(Filter\FilterText $filter): void
	{
		$condition = $filter->getCondition();
		$driver = $this->data_source->getConnection()->getDriver();
		$or = [];

		foreach ($condition as $column => $value) {

			$column = '[' . $column . ']';

			if ($filter->isExactSearch()) {
				$this->data_source->where("$column = %s", $value);
				continue;
			}

			if ($filter->hasSplitWordsSearch() === false) {
				$words = [$value];
			} else {
				$words = explode(' ', $value);
			}

			foreach ($words as $word) {
				$escaped = $driver->escapeLike($word, 0);
				$or[] = "$column ILIKE $escaped";
			}
		}

		if (sizeof($or) > 1) {
			$this->data_source->where('(%or)', $or);
		} else {
			$this->data_source->where($or);
		}
	}

}
