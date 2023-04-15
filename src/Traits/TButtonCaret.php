<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Traits;

trait TButtonCaret
{

	protected bool $caret = true;

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
