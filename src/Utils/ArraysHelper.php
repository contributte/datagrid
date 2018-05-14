<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Utils;

use Nette\SmartObject;
use Traversable;

final class ArraysHelper
{

	use SmartObject;

	/**
	 * Test recursively whether given array is empty
	 *
	 * @param  array $array
	 */
	public static function testEmpty(array $array): bool
	{
		foreach ($array as $key => $value) {
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
	 *
	 * @param  array|Traversable $a
	 */
	public static function testTruthy($a): bool
	{
		foreach ($a as $value) {
			if (is_array($value) || $value instanceof Traversable) {
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
