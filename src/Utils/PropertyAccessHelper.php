<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\Utils;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

final class PropertyAccessHelper
{

	/**
	 * @var PropertyAccessor
	 */
	private static $accessor = null;

	public static function getAccessor(): PropertyAccessor
	{
		if (self::$accessor === null) {
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
