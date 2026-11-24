<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Traits;

trait TButtonClass
{

	protected string $class = 'btn btn-xs btn-default btn-secondary';

	/**
	 * @return static
	 */
	public function setClass(string $class): self
	{
		$this->class = $class;

		return $this;
	}

	/**
	 * Append class(es) to the current button class
	 */
	public function addClass(string $class): static
	{
		$class = trim($class);

		if ($class === '') {
			return $this;
		}

		$current = trim($this->class);
		$this->class = $current === '' ? $class : $current . ' ' . $class;

		return $this;
	}

	public function getClass(): string
	{
		return $this->class;
	}

}
