<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Utils;

use Nette;

final class ArraysHelper extends Nette\Object
{

	/**
	 * Test recursively whether given array is empty
	 * @param  array $array
	 * @return bool
	 */
	public static function testEmpty($array)
	{
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				if (!self::testEmpty($value)) {
					return FALSE;
				}
			} else {
				if ($value) {
					return FALSE;
				}
			}
		}

		return TRUE;
	}


	/**
	 * Is array and its values truthy?
	 * @param  array|\Traversable $a
	 * @return boolean
	 */
	public static function testTruthy($a)
	{
		foreach ($a as $value) {
			if (is_array($value) || $value instanceof \Traversable) {
				if (self::testTruthy($value)) {
					return TRUE;
				}
			} else {
				if ($value !== '' && $value !== NULL) {
					return TRUE;
				}
			}
		}

		return FALSE;
	}

}
