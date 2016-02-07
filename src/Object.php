<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid;

use Nette;

class Object extends Nette\Object
{

	/**
	 * Allows calling $column->icon() instead of $column->setIcon (Same for title, class, ...)
	 * @param  string $name
	 * @param  array  $args
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		$method_setter = 'set'.ucfirst($name);

		if (method_exists($this, $method_setter)) {
			return Nette\Utils\Callback::invokeArgs([$this, $method_setter], $args);
		}

		return parent::__call($name, $args);
	}

}
