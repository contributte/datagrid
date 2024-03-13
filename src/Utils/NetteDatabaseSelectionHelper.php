<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Utils;

use Nette\Database\Driver;
use Nette\Database\Explorer;
use Nette\Database\Table\Selection;
use ReflectionClass;

final class NetteDatabaseSelectionHelper
{

	public static function getDriver(Selection $selection): Driver
	{
		$connection = self::getContext($selection)->getConnection();

		return $connection->getDriver();
	}

	public static function getContext(Selection $selection): Explorer
	{
		$reflection = new ReflectionClass($selection);

		$explorerProperty = $reflection->getProperty('explorer');
		$explorerProperty->setAccessible(true);

		return $explorerProperty->getValue($selection);
	}

}
