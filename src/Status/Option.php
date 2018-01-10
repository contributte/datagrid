<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Status;

use Nette\SmartObject;
use Ublaboo\DataGrid\Column\ColumnStatus;

class Option
{

	use SmartObject;

	/**
	 * @var ColumnStatus
	 */
	protected $columnStatus;

	/**
	 * @var mixed
	 */
	protected $value;

	/**
	 * @var string
	 */
	protected $text;

	/**
	 * @var string|callable
	 */
	protected $title;

	/**
	 * @var string|callable
	 */
	protected $class = 'btn-success';

	/**
	 * @var string
	 */
	protected $class_secondary = 'btn btn-xs';

	/**
	 * @var string
	 */
	protected $class_in_dropdown = 'ajax';

	/**
	 * @var string
	 */
	protected $icon;

	/**
	 * @var string
	 */
	protected $icon_secondary;


	/**
	 * [__construct description]
	 * @param ColumnStatus $columnStatus
	 * @param mixed       $value
	 * @param string       $text
	 */
	public function __construct(ColumnStatus $columnStatus, $value, $text)
	{
		$this->columnStatus = $columnStatus;
		$this->value = $value;
		$this->text = (string) $text;
	}


	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}


	/**
	 * End option fluent interface and return parent
	 * @return ColumnStatus
	 */
	public function endOption()
	{
		return $this->columnStatus;
	}


	/**
	 * @param string $title
	 * @return static
	 */
	public function setTitle($title)
	{
		$this->title = (string) $title;

		return $this;
	}


	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}


	/**
	 * @param string $class
	 * @param string $class_secondary
	 * @return static
	 */
	public function setClass($class, $class_secondary = null)
	{
		$this->class = (string) $class;

		if ($class_secondary !== null) {
			$this->class_secondary = (string) $class_secondary;
		}

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
	 * @param string $class_secondary
	 * @return static
	 */
	public function setClassSecondary($class_secondary)
	{
		$this->class_secondary = (string) $class_secondary;

		return $this;
	}


	/**
	 * @return string
	 */
	public function getClassSecondary()
	{
		return $this->class_secondary;
	}


	/**
	 * @param string $class_in_dropdown
	 * @return static
	 */
	public function setClassInDropdown($class_in_dropdown)
	{
		$this->class_in_dropdown = (string) $class_in_dropdown;

		return $this;
	}


	/**
	 * @return string
	 */
	public function getClassInDropdown()
	{
		return $this->class_in_dropdown;
	}


	/**
	 * @param string $icon
	 * @return static
	 */
	public function setIcon($icon)
	{
		$this->icon = (string) $icon;

		return $this;
	}


	/**
	 * @return string|NULL
	 */
	public function getIcon()
	{
		return $this->icon;
	}


	/**
	 * @param string $icon_secondary
	 */
	public function setIconSecondary($icon_secondary)
	{
		$this->icon_secondary = (string) $icon_secondary;

		return $this;
	}


	/**
	 * @return string|NULL
	 */
	public function getIconSecondary()
	{
		return $this->icon_secondary;
	}


	/**
	 * @return string
	 */
	public function getText()
	{
		return $this->text;
	}
}
