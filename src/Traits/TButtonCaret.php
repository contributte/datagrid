<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\Traits;

trait TButtonCaret
{

	/**
	 * @var bool
	 */
	protected $caret = true;

	/**
	 * @return static
	 */
	public function setCaret(bool $useCaret): self
	{
		$this->caret = $useCaret;

		return $this;
	}


	public function hasCaret(): bool
	{
		return $this->caret;
	}

}
