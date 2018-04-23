<?php declare(strict_types = 1);

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

	/**
	 * Should be a "caret" present in status dropdown?
	 *
	 * @param bool $use_caret
	 * @return static
	 */
	public function setCaret(bool $use_caret)
	{
		$this->caret = (bool) $use_caret;

		return $this;
	}


	/**
	 * @return bool
	 */
	public function hasCaret(): bool
	{
		return $this->caret;
	}

}
