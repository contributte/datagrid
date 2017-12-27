<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\DataSource;

use Nette\SmartObject;
use Nette\Utils\Callback;
use Ublaboo\DataGrid\Filter;

abstract class FilterableDataSource
{

	use SmartObject;

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
					} elseif ($filter instanceof Filter\FilterMultiSelect) {
						$this->applyFilterMultiSelect($filter);
					} elseif ($filter instanceof Filter\FilterSelect) {
						$this->applyFilterSelect($filter);
					} elseif ($filter instanceof Filter\FilterDate) {
						$this->applyFilterDate($filter);
					} elseif ($filter instanceof Filter\FilterDateRange) {
						$this->applyFilterDateRange($filter);
					} elseif ($filter instanceof Filter\FilterRange) {
						$this->applyFilterRange($filter);
					}
				}
			}
		}

		return $this;
	}
}
