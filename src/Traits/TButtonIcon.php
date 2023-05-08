<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Traits;

trait TButtonIcon
{

	protected ?string $icon = null;

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
