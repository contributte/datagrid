<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Traits;

use Ublaboo\DataGrid\DataGrid;
use Nette\Utils\Html;
use Ublaboo\DataGrid\Row;

trait ButtonIconTrait
{

	/**
	 * Should the element has an icon?
	 * @param  Html            $el
	 * @param  string|null     $icon
	 * @param  string          $name
	 * @return void
	 */
	public function tryAddIcon($el, $icon, $name)
	{
		if ($icon) {
			$el->add(Html::el('span')->class(DataGrid::$icon_prefix.$icon));

			if (strlen($name)) {
				$el->add('&nbsp;');
			}
		}
	}

}
