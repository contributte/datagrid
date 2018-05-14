<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Traits;

trait TButtonIcon
{

	/**
	 * @var string|callable
	 */
	protected $icon;

	/**
	 * Set icon
	 */
	public function setIcon(string $icon)
	{
		$this->icon = $icon;

		return $this;
	}


	/**
	 * Get icon
	 */
	public function getIcon(): string
	{
		return $this->icon;
	}

}
