<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\Traits;

trait TButtonClass
{

	/**
	 * @var string
	 */
	protected $class = 'btn btn-xs btn-default btn-secondary';

	/**
	 * @return static
	 */
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
