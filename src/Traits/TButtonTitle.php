<?php declare(strict_types = 1);

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Traits;

trait TButtonTitle
{

	/**
	 * @var string|callable
	 */
	protected $title = '';

	/**
	 * Set attribute title
	 *
	 * @param string $title
	 */
	public function setTitle(string $title)
	{
		$this->title = $title;

		return $this;
	}


	/**
	 * Get attribute title
	 *
	 * @return string
	 */
	public function getTitle(): string
	{
		return $this->title;
	}

}
