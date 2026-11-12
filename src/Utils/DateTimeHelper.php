<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Utils;

use Contributte\Datagrid\Exception\DatagridDateTimeHelperException;
use DateTime;
use DateTimeImmutable;

final class DateTimeHelper
{

	/**
	 * Parse the configured filter format, allowing an omitted trailing year in numeric dates.
	 *
	 * The reference date supplies the year and timezone only for an omitted year.
	 *
	 * @throws DatagridDateTimeHelperException
	 */
	public static function tryConvertToFilterDate(
		mixed $value,
		string $format,
		?DateTimeImmutable $referenceDate = null
	): DateTime
	{
		if ($value instanceof DateTime || $value instanceof DateTimeImmutable) {
			return self::fromString($value);
		}

		$value = (string) $value;
		if (str_contains($value, "\0")) {
			throw new DatagridDateTimeHelperException();
		}

		$date = DateTime::createFromFormat('!' . $format, $value);
		$errors = DateTime::getLastErrors();
		if ($date !== false && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
			return $date;
		}

		if (
			preg_match('~^([djmn])([./\s-]+)([djmn])([./\s-]+)[Yy]$~D', $format, $parts) !== 1
			|| str_contains('dj', $parts[1]) === str_contains('dj', $parts[3])
		) {
			throw new DatagridDateTimeHelperException();
		}

		$separator = trim($parts[2]);
		$separatorPattern = $separator === '' ? '\s+' : '\s*' . preg_quote($separator, '~') . '\s*';
		$yearSeparator = trim($parts[4]);
		$trailingSeparator = $yearSeparator === '' ? '' : '(?:\s*' . preg_quote($yearSeparator, '~') . ')?';

		if (preg_match('~^\s*(\d{1,2})' . $separatorPattern . '(\d{1,2})' . $trailingSeparator . '\s*$~D', $value, $matches) !== 1) {
			throw new DatagridDateTimeHelperException();
		}

		$referenceDate ??= new DateTimeImmutable();
		$year = (int) $referenceDate->format('Y');
		$day = (int) $matches[str_contains('dj', $parts[1]) ? 1 : 2];
		$month = (int) $matches[str_contains('dj', $parts[1]) ? 2 : 1];

		if (!checkdate($month, $day, $year)) {
			throw new DatagridDateTimeHelperException();
		}

		return (new DateTime('now', $referenceDate->getTimezone()))->setDate($year, $month, $day)->setTime(0, 0);
	}

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
