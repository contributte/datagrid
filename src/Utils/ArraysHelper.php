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

}
