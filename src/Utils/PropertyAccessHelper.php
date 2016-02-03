<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Utils;

use Nette;
use Symfony\Component\PropertyAccess\PropertyAccess;

final class PropertyAccessHelper extends Nette\Object
{

	private static $accessor = NULL;


	public static function getAccessor()
	{
		if (!self::$accessor) {
			self::$accessor = PropertyAccess::createPropertyAccessor();
		}

		return self::$accessor;
	}

}
