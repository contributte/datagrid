<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Column;

use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Row;
use Ublaboo\DataGrid\Exception\DataGridHasToBeAttachedToPresenterComponentException;

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
	 * @param  string $href
	 * @param  array  $params
	 * @return string
	 * @throws DataGridHasToBeAttachedToPresenterComponentException
	 * @throws InvalidArgumentException
	 */
	protected function createLink($href, $params)
	{
		/**
		 * Int case of ActionCallback, $this->href is a identifier of user callback
		 */
		$params = $params + ['__key' => $this->href];

		return $this->grid->link('actionCallback!', $params);
	}

}
