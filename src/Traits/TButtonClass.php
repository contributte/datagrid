<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Traits;

trait TButtonClass
{

	/**
	 * @var string
	 */
	protected $class = 'btn btn-xs btn-default btn-secondary';

	/**
	 * Set attribute class
	 */
	public function setClass(string $class)
	{
		$this->class = $class;

		return $this;
	}


	/**
	 * Get attribute class
	 */
	public function getClass(): string
	{
		return $this->class;
	}

}
