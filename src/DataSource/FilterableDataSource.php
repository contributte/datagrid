<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\DataSource;

use DibiFluent;
use Nette\Utils\Callback;
use Nette\Utils\Strings;
use Ublaboo\DataGrid\Filter;

abstract class FilterableDataSource
{

	/**
	 * Filter data
	 * @param array $filters
	 * @return static
	 */
	public function filter(array $filters)
	{
		foreach ($filters as $filter) {
			if ($filter->isValueSet()) {
				if ($filter->hasConditionCallback()) {
					Callback::invokeArgs(
						$filter->getConditionCallback(),
						[$this->data_source, $filter->getValue()]
					);
				} else {
					if ($filter instanceof Filter\FilterText) {
						$this->applyFilterText($filter);
					} else if ($filter instanceof Filter\FilterSelect) {
						$this->applyFilterSelect($filter);
					} else if ($filter instanceof Filter\FilterDate) {
						$this->applyFilterDate($filter);
					} else if ($filter instanceof Filter\FilterDateRange) {
						$this->applyFilterDateRange($filter);
					} else if ($filter instanceof Filter\FilterRange) {
						$this->applyFilterRange($filter);
					}
				}
			}
		}

		return $this;
	}

}
