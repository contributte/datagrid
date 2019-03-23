<?php declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\GroupAction;


/**
 * @method void onSelect(array $ids, string $value)
 */
abstract class GroupAction
{

	use SmartObject;

	/**
	 * @var callable[]
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


	public function setClass(string $class): self
	{
		$this->class = (string) $class;

		return $this;
	}


	public function getClass(): string
	{
		return $this->class;
	}


	/**
	 * @param string $key
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
