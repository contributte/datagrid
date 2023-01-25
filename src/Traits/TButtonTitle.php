<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Traits;

trait TButtonTitle
{

	protected ?string $title = null;

	/**
	 * @return static
	 */
	public function setTitle(string $title): self
	{
		$this->title = $title;

		return $this;
	}

	public function getTitle(): ?string
	{
		return $this->title;
	}

}
