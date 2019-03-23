<?php declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Traits;

trait TButtonTitle
{

	/**
	 * @var string
	 */
	protected $title = '';


	public function setTitle(string $title): self
	{
		$this->title = $title;

		return $this;
	}


	public function getTitle(): string
	{
		return $this->title;
	}
}
