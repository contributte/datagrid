<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\GroupAction;

use Nette\SmartObject;

/**
 * @method void onSelect(array $ids, string $value)
 */
abstract class GroupAction
{

	use SmartObject;

	/**
	 * @var array|callable[]
	 */
	public $onSelect = [];

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $class = 'form-control input-sm form-control-sm';

	/**
	 * @var array
	 */
	protected $attributes = [];


	public function __construct(string $title)
	{
		$this->title = $title;
	}


	public function getTitle(): string
	{
		return $this->title;
	}


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


	/**
	 * @param mixed $value
	 * @return static
	 */
	public function setAttribute(string $key, $value): self
	{
		$this->attributes[$key] = $value;

		return $this;
	}


	public function getAttributes(): array
	{
		return $this->attributes;
	}
}
