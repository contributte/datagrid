<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Utils;

final class ArraysHelper
{

	/**
	 * Test recursively whether given array is empty
	 */
	public static function testEmpty(iterable $array): bool
	{
		foreach ($array as $value) {
			if (is_array($value)) {
				if (!self::testEmpty($value)) {
					return false;
				}
			} else {
				if ($value) {
					return false;
				}

				if (in_array($value, [0, '0', false], true)) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Is array and its values truthy?
	 */
	public static function testTruthy(iterable $iterable): bool
	{
		foreach ($iterable as $value) {
			if (is_iterable($value)) {
				if (self::testTruthy($value)) {
					return true;
				}
			} else {
				if ($value !== '' && $value !== null) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Recursively drop empty values ('' / null / empty arrays), cast scalars to string and sort by keys,
	 * so two arrays of filter values can be strictly compared
	 *
	 * @param array<mixed> $array
	 * @return array<mixed>
	 */
	public static function normalizeValues(array $array): array
	{
		$normalized = [];

		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$value = self::normalizeValues($value);

				if ($value === []) {
					continue;
				}
			} elseif ($value === '' || $value === null) {
				continue;
			} elseif (is_scalar($value)) {
				$value = (string) $value;
			}

			$normalized[$key] = $value;
		}

		ksort($normalized);

		return $normalized;
	}

}
