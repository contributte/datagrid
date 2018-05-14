<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Traits;

trait TButtonTitle
{

	/**
	 * @var string|callable
	 */
	protected $title = '';

	/**
	 * Set attribute title
	 */
	public function setTitle(string $title)
	{
		$this->title = $title;

		return $this;
	}


	/**
	 * Get attribute title
	 */
	public function getTitle(): string
	{
		return $this->title;
	}

}
