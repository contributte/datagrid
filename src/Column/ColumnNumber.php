<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Column;

class ColumnNumber extends Column
{

	/**
	 * @var array
	 */
	protected $number_format = [
		0, // Decimals
		'.', // Decimal point
		' '  // Thousands separator
	];


	/**
	 * Formatitem value
	 * @param  mixd  $item
	 * @return mixed
	 */
	public function getColumnValue($item)
	{
		$value = parent::getColumnValue($item);

		if (!is_numeric($value)) {
			return $value;
		}

		return number_format(
			$value,
			$this->number_format[0],
			$this->number_format[1],
			$this->number_format[2]
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

}
