<?php declare(strict_types = 1);

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
	protected $class_in_dropdown = 'ajax dropdown-item';

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
	 *
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
	 */
	public function endOption(): ColumnStatus
	{
		return $this->columnStatus;
	}


	/**
	 * @return static
	 */
	public function setTitle(string $title)
	{
		$this->title = (string) $title;

		return $this;
	}


	public function getTitle(): string
	{
		return $this->title;
	}


	/**
	 * @return static
	 */
	public function setClass(string $class, ?string $class_secondary = null)
	{
		$this->class = (string) $class;

		if ($class_secondary !== null) {
			$this->class_secondary = (string) $class_secondary;
		}

		return $this;
	}


	public function getClass(): string
	{
		return $this->class;
	}


	/**
	 * @return static
	 */
	public function setClassSecondary(string $class_secondary)
	{
		$this->class_secondary = (string) $class_secondary;

		return $this;
	}


	public function getClassSecondary(): string
	{
		return $this->class_secondary;
	}


	/**
	 * @return static
	 */
	public function setClassInDropdown(string $class_in_dropdown)
	{
		$this->class_in_dropdown = (string) $class_in_dropdown;

		return $this;
	}


	public function getClassInDropdown(): string
	{
		return $this->class_in_dropdown;
	}


	/**
	 * @return static
	 */
	public function setIcon(string $icon)
	{
		$this->icon = (string) $icon;

		return $this;
	}


	public function getIcon(): ?string
	{
		return $this->icon;
	}


	public function setIconSecondary(string $icon_secondary)
	{
		$this->icon_secondary = (string) $icon_secondary;

		return $this;
	}


	public function getIconSecondary(): ?string
	{
		return $this->icon_secondary;
	}


	public function getText(): string
	{
		return $this->text;
	}

}
