<?php declare(strict_types = 1);

namespace Contributte\Datagrid\GroupAction;

use Nette\SmartObject;

/**
 * @method void onSelect(array $ids, string $value)
 */
abstract class GroupAction
{

	use SmartObject;

	/** @var array|callable[] */
	public array $onSelect = [];

	protected string $class = 'form-control input-sm form-control-sm';

	protected array $attributes = [];

	public function __construct(protected string $title)
	{
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
	 * @return static
	 */
	public function setAttribute(string $key, mixed $value): self
	{
		$this->attributes[$key] = $value;

		return $this;
	}

	public function getAttributes(): array
	{
		return $this->attributes;
	}

}
