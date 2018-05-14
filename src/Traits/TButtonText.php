<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Traits;

trait TButtonText
{

	/**
	 * @var string
	 */
	protected $text = '';

	/**
	 * Set text
	 */
	public function setText(string $text)
	{
		$this->text = $text;

		return $this;
	}


	/**
	 * Get text
	 */
	public function getText(): string
	{
		return $this->text;
	}

}
