<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Column;

use Ublaboo\DataGrid\Row;

class ColumnNumber extends Column
{
	/**
	 * @var string
	 */
	protected $align = 'right';

	/**
	 * @var array
	 */
	protected $number_format = [
		0, // Decimals
		'.', // Decimal point
		' ',  // Thousands separator
	];


	/**
	 * Format row item value
	 * @param  Row   $row
	 * @return mixed
	 */
	public function getColumnValue(Row $row)
	{
		$value = parent::getColumnValue($row);

		if (!is_numeric($value)) {
			return $value;
		}

		return number_format(
			(float) $value,
			(int) $this->number_format[0],
			(string) $this->number_format[1],
			(string) $this->number_format[2]
		);
	}


	/**
	 * Set number format
	 * @param int    $decimals
	 * @param string $dec_point
	 * @param string $thousands_sep
	 */
	public function setFormat($decimals = 0, $dec_point = '.', $thousands_sep = ' ')
	{
		$this->number_format = [$decimals, $dec_point, $thousands_sep];

		return $this;
	}


	/**
	 * @return array
	 */
	public function getFormat()
	{
		return [
			$this->number_format[0],
			$this->number_format[1],
			$this->number_format[2],
		];
	}
}
