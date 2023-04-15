<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Traits;

trait TButtonText
{

	protected string $text = '';

	/**
	 * @return static
	 */
	public function setText(string $text): self
	{
		$this->text = $text;

		return $this;
	}

	public function getText(): string
	{
		return $this->text;
	}

}
