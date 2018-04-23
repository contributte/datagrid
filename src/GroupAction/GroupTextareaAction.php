<?php declare(strict_types = 1);

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
	public function __construct(string $title)
	{
		parent::__construct($title);
	}

}
