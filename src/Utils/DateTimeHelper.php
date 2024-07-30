<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\Utils;

use DateTimeImmutable;
use Ublaboo\DataGrid\Exception\DataGridDateTimeHelperException;

final class DateTimeHelper
{

	/**
	 * Try to convert string into \DateTime object
	 *
	 * @param mixed $value
	 * @param array|string[] $formats
	 * @throws DataGridDateTimeHelperException
	 */
	public static function tryConvertToDateTime($value, array $formats = []): \DateTime
	{
		return self::fromString($value, $formats);
	}


	/**
	 * Try to convert string into \DateTime object from more date formats
	 *
	 * @param mixed $value
	 * @param array|string[] $formats
	 * @throws DataGridDateTimeHelperException
	 */
	public static function tryConvertToDate($value, array $formats = []): \DateTime
	{
		return self::fromString($value, $formats);
	}


	/**
	 * Convert string into \DateTime object from more date without time
	 *
	 * @param mixed $value
	 * @param array|string[] $formats
	 * @throws DataGridDateTimeHelperException
	 */
	public static function fromString($value, array $formats = []): \DateTime
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

		if ($value instanceof \DateTime) {
			return $value;
		}

		if ($value instanceof DateTimeImmutable) {
			/** @var \DateTimeZone|false $tz */
			$tz = $value->getTimezone();
			$date = new \DateTime('now', $tz !== false ? $tz : null);
			$date->setTimestamp($value->getTimestamp());

			return $date;
		}

		foreach ($formats as $format) {
			$date = \DateTime::createFromFormat($format, (string) $value);

			if ($date === false) {
				continue;
			}

			return $date;
		}

		$timestamp = strtotime((string) $value);

		if ($timestamp !== false) {
			$date = new \DateTime;
			$date->setTimestamp($timestamp);

			return $date;
		}

		throw new DataGridDateTimeHelperException();
	}

}
