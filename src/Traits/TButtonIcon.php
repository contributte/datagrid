<?php declare(strict_types=1);

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


	public function setIcon(string $icon): self
	{
		$this->icon = $icon;

		return $this;
	}


	public function getIcon(): self
	{
		return $this->icon;
	}
}
