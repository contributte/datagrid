<?php declare(strict_types = 1);

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Traits;

trait TButtonIcon
{

	/**
	 * @var string|callable
	 */
	protected $icon;

	/**
	 * Set icon
	 *
	 * @param string $icon
	 */
	public function setIcon(string $icon)
	{
		$this->icon = $icon;

		return $this;
	}


	/**
	 * Get icon
	 *
	 * @return string
	 */
	public function getIcon(): string
	{
		return $this->icon;
	}

}
