<?php declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Traits;

trait TButtonClass
{

	/**
	 * @var string
	 */
	protected $class = 'btn btn-xs btn-default btn-secondary';


	public function setClass(string $class): self
	{
		$this->class = $class;

		return $this;
	}


	public function getClass(): string
	{
		return $this->class;
	}
}
