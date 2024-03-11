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
