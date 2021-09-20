<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\GroupAction;

/**
 * @method void onSelect()
 */
class GroupTextareaAction extends GroupAction
{

	/**
	 * @param string $title
	 */
	public function __construct($title)
	{
		parent::__construct($title);
	}
}
