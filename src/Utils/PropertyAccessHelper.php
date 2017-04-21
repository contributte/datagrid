<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Utils;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

final class PropertyAccessHelper
{
	/**
	 * @var PropertyAccessor
	 */
	private static $accessor;


	/**
	 * @return PropertyAccessor
	 */
	public static function getAccessor()
	{
		if (!self::$accessor) {
			self::$accessor = PropertyAccess::createPropertyAccessor();
		}

		return self::$accessor;
	}


	/**
	 * @param  object  $class
	 * @param  string  $property
	 * @return mixed
	 */
	public static function getValue($class, $property)
	{
		return self::getAccessor()->getValue($class, $property);
	}
}
