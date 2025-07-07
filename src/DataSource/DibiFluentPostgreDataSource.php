<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\DataSource;

use Dibi;
use DibiFluent;
use Ublaboo\DataGrid\Filter;

class DibiFluentPostgreDataSource extends DibiFluentDataSource
{
	/**
	 * Filter by keyword
	 * @param  Filter\FilterText $filter
	 * @return void
	 */
	public function applyFilterText(Filter\FilterText $filter)
	{
		$condition = $filter->getCondition();
		$driver = $this->data_source->getConnection()->getDriver();
		$or = [];

		$is_negation_search = false;
		foreach ($condition as $column => $value) {
			if ($filter->isSpecialChars()) {
				if ($value === Filter\FilterText::TOKEN_EMPTY) { // Handle single '#'
					$this->data_source->where("($column IS NULL OR $column = '')");
					continue;
				} else if ($value === Filter\FilterText::TOKEN_NEGATION . Filter\FilterText::TOKEN_EMPTY) {
					$this->data_source->where("($column IS NOT NULL AND $column <> '')");
					continue;
				}
				$value = str_replace(Filter\FilterText::TOKEN_EMPTY_ESCAPED, Filter\FilterText::TOKEN_EMPTY, $value);
			}
			$column = '[' . $column . ']::varchar';

			if ($filter->isExactSearch()) {
				$this->data_source->where("$column = %s", $value);
				continue;
			}

			if ($filter->hasSplitWordsSearch() === false) {
				$words = [$value];
			} else {
				$words = explode(' ', $value);
			}
			$x = [];
			foreach ($words as $word) {
				$escaped = $driver->escapeLike((string) $word, 0);
				if ($filter->isSpecialChars()) {
					$allow_negation_filter = true;
					if (strpos($word, Filter\FilterText::TOKEN_NEGATION_ESCAPED) !== false) {
						//If the escaped negation token is in the beginning of text, explicitly forbid parsing it after it's replaced
						if (strpos($word, Filter\FilterText::TOKEN_NEGATION_ESCAPED) === 0) {
							$allow_negation_filter = false;
						}

						$word = str_replace(Filter\FilterText::TOKEN_NEGATION_ESCAPED, Filter\FilterText::TOKEN_NEGATION, $word);
						$escaped = $driver->escapeLike($word, 0);
					}

					if ($allow_negation_filter && strpos($word, Filter\FilterText::TOKEN_NEGATION) === 0) {
						//exclamation point means negation - the word is NOT included in the searched string
						$escaped = $driver->escapeLike(substr($escaped, 2, -1),0);
						$x[] = "($column IS NULL OR $column = '' OR public.unaccent($column) NOT ILIKE public.unaccent('%' || " . $escaped . " || '%'))";
						continue;
					}
				}

				$x[] = "public.unaccent($column) ILIKE public.unaccent('%" . substr($escaped, 1, -1) . "%')";
			}
			$or[] = "((" . implode(") AND (", $x) . "))";
		}

		if ($is_negation_search) {
			$condition = sprintf("(%s)", implode(' AND ', $or));
			$this->data_source->where($condition);
		} else if (sizeof($or) > 1) {
			$this->data_source->where('(%or)', $or);
		} else {
			$this->data_source->where($or);
		}
	}
}
