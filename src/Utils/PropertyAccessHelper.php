<?php declare(strict_types = 1);

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
	 * @param  object  $class
	 * @param  string  $property
	 * @return mixed
	 */
	public static function getValue($class, $property)
	{
		return self::getAccessor()->getValue($class, $property);
	}

}
