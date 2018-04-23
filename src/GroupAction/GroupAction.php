<?php declare(strict_types = 1);

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\GroupAction;

use Nette\SmartObject;

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
	protected $class = 'form-control input-sm';

	/**
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * @param string $title
	 */
	public function __construct(string $title)
	{
		$this->title = $title;
	}


	/**
	 * Get action title
	 *
	 * @return string
	 */
	public function getTitle(): string
	{
		return $this->title;
	}


	/**
	 * @param string $class
	 * @return static
	 */
	public function setClass(string $class)
	{
		$this->class = (string) $class;

		return $this;
	}


	/**
	 * @return string
	 */
	public function getClass(): string
	{
		return $this->class;
	}


	/**
	 * @param string $key
	 * @param mixed $value
	 * @return static
	 */
	public function setAttribute(string $key, $value)
	{
		$this->attributes[$key] = $value;

		return $this;
	}


	/**
	 * @return array
	 */
	public function getAttributes(): array
	{
		return $this->attributes;
	}

}
