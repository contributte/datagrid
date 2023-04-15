<?php declare(strict_types = 1);

namespace Contributte\Datagrid\DataSource;

use Contributte\Datagrid\Filter\FilterText;

class DibiFluentPostgreDataSource extends DibiFluentDataSource
{

	protected function applyFilterText(FilterText $filter): void
	{
		$condition = $filter->getCondition();
		$driver = $this->dataSource->getConnection()->getDriver();
		$or = [];

		foreach ($condition as $column => $value) {

			$column = '[' . $column . ']';

			if ($filter->isExactSearch()) {
				$this->dataSource->where(sprintf('%s = %%s', $column), $value);

				continue;
			}

			$words = $filter->hasSplitWordsSearch() === false ? [$value] : explode(' ', $value);

			foreach ($words as $word) {
				$or[] = $column . ' ILIKE ' . $driver->escapeText('%' . $word . '%');
			}
		}

		if (count($or) > 1) {
			$this->dataSource->where('(%or)', $or);
		} else {
			$this->dataSource->where($or);
		}
	}

}
