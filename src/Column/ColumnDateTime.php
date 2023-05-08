<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Column;

use Contributte\Datagrid\Exception\DatagridDateTimeHelperException;
use Contributte\Datagrid\Row;
use Contributte\Datagrid\Utils\DateTimeHelper;
use DateTime;

class ColumnDateTime extends Column
{

	protected ?string $align = 'end';

	protected string $format = 'j. n. Y';

	public function getColumnValue(Row $row): string
	{
		$value = parent::getColumnValue($row);

		if (!($value instanceof DateTime)) {
			/**
			 * Try to convert string to DateTime object
			 */
			try {
				$date = DateTimeHelper::tryConvertToDateTime($value);

				return $date->format($this->format);
			} catch (DatagridDateTimeHelperException) {
				/**
				 * Otherwise just return raw string
				 */
				return (string) $value;
			}
		}

		return $value->format($this->format);
	}

	/**
	 * @return static
	 */
	public function setFormat(string $format): self
	{
		$this->format = $format;

		return $this;
	}

}
