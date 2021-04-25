<?php

declare(strict_types=1);

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
	protected $numberFormat = [
		0, // Decimals
		'.', // Decimal point
		' ', // Thousands separator
	];

	/**
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
			(int) $this->numberFormat[0],
			(string) $this->numberFormat[1],
			(string) $this->numberFormat[2]
		);
	}


	/**
	 * @return static
	 */
	public function setFormat(
		int $decimals = 0,
		string $decPoint = '.',
		string $thousandsSep = ' '
	): self
	{
		$this->numberFormat = [$decimals, $decPoint, $thousandsSep];

		return $this;
	}


	public function getFormat(): array
	{
		return [
			$this->numberFormat[0],
			$this->numberFormat[1],
			$this->numberFormat[2],
		];
	}
}
