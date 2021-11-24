<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Traits;

use Ublaboo\DataGrid\Utils\IconData;

trait TButtonIcon
{

	/**
	 * @var string|callable
	 */
	protected $icon;


	/**
	 * Set icon
	 * @param string $icon
	 * @param string $content
	 */
	public function setIcon($icon, $content = '')
	{
		$this->icon = new IconData($icon, $content);

		return $this;
	}


	/**
	 * Get icon
	 * @return string
	 */
	public function getIcon()
	{
		return $this->icon;
	}
}
