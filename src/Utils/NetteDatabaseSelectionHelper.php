<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Utils;

use Nette\Database\Table\Selection;

final class NetteDatabaseSelectionHelper
{
	public static function getDriver(Selection $selection)
	{
		$connection = self::getContext($selection)->getConnection();

		return $connection->getSupplementalDriver();
	}


	public static function getContext(Selection $selection)
	{
		$reflection = new \ReflectionClass($selection);

		$context_property = $reflection->getProperty('context');
		$context_property->setAccessible(true);

		return $context_property->getValue($selection);
	}
}
