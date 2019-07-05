<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\Traits;

trait TButtonIcon
{

	/**
	 * @var string|null
	 */
	protected $icon;

	/**
	 * @return static
	 */
	public function setIcon(?string $icon): self
	{
		$this->icon = $icon;

		return $this;
	}


	public function getIcon(): ?string
	{
		return $this->icon;
	}

}
