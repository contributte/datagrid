<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Traits;

trait TButtonCaret
{

	/**
	 * @var boolean
	 */
	protected $caret = true;


	/**
	 * Should be a "caret" present in status dropdown?
	 * @param bool $use_caret
	 * @return static
	 */
	public function setCaret($use_caret)
	{
		$this->caret = (bool) $use_caret;

		return $this;
	}


	/**
	 * @return boolean
	 */
	public function hasCaret()
	{
		return $this->caret;
	}
}
