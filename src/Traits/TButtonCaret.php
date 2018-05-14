<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Traits;

trait TButtonCaret
{

	/**
	 * @var bool
	 */
	protected $caret = true;

	/**
	 * Should be a "caret" present in status dropdown?
	 *
	 * @return static
	 */
	public function setCaret(bool $use_caret)
	{
		$this->caret = (bool) $use_caret;

		return $this;
	}


	public function hasCaret(): bool
	{
		return $this->caret;
	}

}
