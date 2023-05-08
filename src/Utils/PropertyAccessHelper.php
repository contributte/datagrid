<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Utils;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

final class PropertyAccessHelper
{

	private static ?PropertyAccessor $accessor = null;

	public static function getAccessor(): PropertyAccessor
	{
		if (self::$accessor === null) {
			self::$accessor = PropertyAccess::createPropertyAccessor();
		}

		return self::$accessor;
	}

	public static function getValue(object $class, string $property): mixed
	{
		return self::getAccessor()->getValue($class, $property);
	}

}
