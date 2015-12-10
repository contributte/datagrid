<?php

/**
 * * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Utils;

use Nette;

final class ArraysHelper extends Nette\Object
{

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

}