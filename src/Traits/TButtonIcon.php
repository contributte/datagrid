<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Traits;

trait TButtonIcon
{

	/** @var string|callable */
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
