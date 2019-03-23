<?php declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Traits;

trait TButtonCaret
{

	/**
	 * @var bool
	 */
	protected $caret = true;


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
