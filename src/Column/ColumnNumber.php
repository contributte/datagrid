<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Column;

use Contributte\Datagrid\Row;

class ColumnNumber extends Column
{

	protected ?string $align = 'end';

	protected array $numberFormat = [
		0, // Decimals
		'.', // Decimal point
		' ', // Thousands separator
	];

	public function getColumnValue(Row $row): mixed
	{
		$value = parent::getColumnValue($row);

		if (!is_numeric($value)) {
			return $value;
		}

		$decimal = null;
		$value = (string) $value;

		if (str_contains($value, '.')) {
			list($integer, $decimal) = explode('.', $value, 2);
		}
		else {
			$integer = $value;
		}

		if ($this->numberFormat[0] > 0) {
			$decimal = substr(
				$decimal . str_repeat('0', $this->numberFormat[0]),
				0,
				$this->numberFormat[0]
			);
		}

		$integer = preg_replace('/\B(?=(\d{3})+(?!\d))/', $this->numberFormat[2], $integer);

		return $decimal ? $integer . $this->numberFormat[1] . $decimal : $integer;
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
