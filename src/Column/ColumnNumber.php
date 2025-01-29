<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Column;

use Contributte\Datagrid\Row;

class ColumnNumber extends Column
{

	/**
	 * Maximum safe integer for floating point operations (2^53 - 1).
	 */
	private const PHP_FLOAT_SAFE_MAX = 9007199254740991;

	/**
	 * Minimum safe integer for floating point operations (-(2^53 - 1)).
	 */
	private const PHP_FLOAT_SAFE_MIN = -9007199254740991;

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

		if ($value > self::PHP_FLOAT_SAFE_MIN && $value < self::PHP_FLOAT_SAFE_MAX) {
			return number_format(
				(float) $value,
				(int) $this->numberFormat[0],
				(string) $this->numberFormat[1],
				(string) $this->numberFormat[2]
			);
		}

		return $this->parseBigIntNumber((string) $value);
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

	protected function parseBigIntNumber(string $number): string
	{
		$decimal = null;

		if (str_contains($number, '.')) {
			[$integer, $decimal] = explode('.', $number, 2);
		} else {
			$integer = $number;
		}

		if ($this->numberFormat[0] > 0) {
			$decimal = substr(
				$decimal . str_repeat('0', $this->numberFormat[0]),
				0,
				$this->numberFormat[0]
			);
		}

		$integer = preg_replace('/\B(?=(\d{3})+(?!\d))/', $this->numberFormat[2], $integer);

		if (strlen((string) $decimal) > 0) {
			return $integer . $this->numberFormat[1] . $decimal;
		}

		return (string) $integer;
	}

}
