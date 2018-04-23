<?php declare(strict_types = 1);

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Traits;

trait TButtonText
{

	/**
	 * @var string
	 */
	protected $text = '';

	/**
	 * Set text
	 *
	 * @param string $text
	 */
	public function setText(string $text)
	{
		$this->text = $text;

		return $this;
	}


	/**
	 * Get text
	 *
	 * @return string
	 */
	public function getText(): string
	{
		return $this->text;
	}

}
