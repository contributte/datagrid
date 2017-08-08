<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Traits;

use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridHasToBeAttachedToPresenterComponentException;

trait TLink
{

	/**
	 * Create link to custom destination
	 * @param  DataGrid $grid
	 * @param  string   $href
	 * @param  array    $params
	 * @return string
	 * @throws DataGridHasToBeAttachedToPresenterComponentException
	 * @throws \InvalidArgumentException
	 */
	protected function createLink(DataGrid $grid, $href, $params)
	{
		try {
			$parent = $grid->getParent();

			return $parent->link($href, $params);
		} catch (DataGridHasToBeAttachedToPresenterComponentException $e) {
			$parent = $grid->getPresenter();

		} catch (\InvalidArgumentException $e) {
			$parent = $grid->getPresenter();
		}

		return $parent->link($href, $params);
	}
}
