<?php declare(strict_types=1);

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


	public static function getAccessor(): PropertyAccessor
	{
		if (!self::$accessor) {
			self::$accessor = PropertyAccess::createPropertyAccessor();
		}

		return self::$accessor;
	}


	/**
	 * @return mixed
	 */
	public static function getValue(object $class, string $property)
	{
		return self::getAccessor()->getValue($class, $property);
	}
}
