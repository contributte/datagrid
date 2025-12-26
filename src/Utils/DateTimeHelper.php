<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Utils;

use Contributte\Datagrid\Exception\DatagridDateTimeHelperException;
use DateTime;
use DateTimeImmutable;

final class DateTimeHelper
{

	/**
	 * Try to convert string into \DateTime object
	 *
	 * @param array|string[] $formats
	 * @throws DatagridDateTimeHelperException
	 */
	public static function tryConvertToDateTime(mixed $value, array $formats = []): DateTime
	{
		return self::fromString($value, $formats);
	}

	/**
	 * Try to convert string into \DateTime object from more date formats
	 *
	 * @param array|string[] $formats
	 * @throws DatagridDateTimeHelperException
	 */
	public static function tryConvertToDate(mixed $value, array $formats = []): DateTime
	{
		return self::fromString($value, $formats);
	}

	/**
	 * Convert string into \DateTime object from more date without time
	 *
	 * @param array|string[] $formats
	 * @throws DatagridDateTimeHelperException
	 */
	public static function fromString(mixed $value, array $formats = []): DateTime
	{
		$formats = array_merge($formats, [
			'Y-m-d H:i:s.u',
			'Y-m-d H:i:s',
			'Y-m-d',
			'j. n. Y G:i:s',
			'j. n. Y G:i',
			'j. n. Y',
			'U',
		]);

		if ($value instanceof DateTime) {
			return $value;
		}

		if ($value instanceof DateTimeImmutable) {
			$date = new DateTime('now', $value->getTimezone());
			$date->setTimestamp($value->getTimestamp());

			return $date;
		}

		foreach ($formats as $format) {
			$date = DateTime::createFromFormat($format, (string) $value);

			if ($date === false) {
				continue;
			}

			return $date;
		}

		$timestamp = strtotime((string) $value);

		if ($timestamp !== false) {
			$date = new DateTime();
			$date->setTimestamp($timestamp);

			return $date;
		}

		throw new DatagridDateTimeHelperException();
	}

}
