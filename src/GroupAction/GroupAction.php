<?php

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
	public function __construct($title)
	{
		$this->title = $title;
	}


	/**
	 * Get action title
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}


	/**
	 * @param string $class
	 * @return static
	 */
	public function setClass($class)
	{
		$this->class = (string) $class;

		return $this;
	}


	/**
	 * @return string
	 */
	public function getClass()
	{
		return $this->class;
	}


	/**
	 * @param string $key
	 * @param mixed $value
	 * @return static
	 */
	public function setAttribute($key, $value)
	{
		$this->attributes[$key] = $value;

		return $this;
	}


	/**
	 * @return array
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}
}
