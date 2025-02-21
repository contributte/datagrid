<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\Traits;

trait TButtonTooltip
{

	/**
	 * @var string
	 */
	protected $tooltip = '';

	/**
	 * @return static
	 */
	public function setTooltip(string $tooltip): self
	{
		$this->tooltip = $tooltip;

		return $this;
	}


	public function getTooltip(): string
	{
		return $this->tooltip;
	}

}
