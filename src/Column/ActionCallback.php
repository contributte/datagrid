<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Column;

use Ublaboo\DataGrid\DataGrid;

/**
 * @method void onClick(mixed $id)
 */
class ActionCallback extends Action
{

	/**
	 * @var callable
	 */
	public $onClick = [];


	/**
	 * Create link to datagrid::handleActionCallback() to fire custom callback
	 * @param  DataGrid $grid
	 * @param  string   $href
	 * @param  array    $params
	 * @return string
	 */
	protected function createLink(DataGrid $grid, $href, $params)
	{
		/**
		 * Int case of ActionCallback, $this->href is a identifier of user callback
		 */
		$params = $params + ['__key' => $this->href];

		return $this->grid->link('actionCallback!', $params);
	}
}
