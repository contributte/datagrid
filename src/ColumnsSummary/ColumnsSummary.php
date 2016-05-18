<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\ColumnsSummary;

use Ublaboo\DataGrid\Column\Column;
use Ublaboo\DataGrid\Column\ColumnNumber;
use Ublaboo\DataGrid\Row;

class ColumnsSummary
{
	/**
	 * @var array
	 */
	protected $columns;


	/**
	 * @param array  $columns
	 */
	public function __construct(array $columns)
	{
		$this->columns = array_fill_keys(array_values($columns), 0);
	}


	/**
	 * @param  Column  $column
	 * @return float
	 */
	public function getValue(Column $column)
	{
		$key = $column->getColumnName();

		if (!isset($this->columns[$key])) {
			return NULL;
		}

		$value = $this->columns[$key];

		if ($column instanceof ColumnNumber) {
			$value = $column->formatValue($value);
		}

		return $value;
	}


	/**
	 * @param Row  $row
	 */
	public function summarize(Row $row)
	{
		foreach ($this->columns as $key => $value) {
			if (!is_numeric($value = $row->getValue($key))) {
				continue;
			}

			$this->columns[$key] += $value;
		}
	}
}
