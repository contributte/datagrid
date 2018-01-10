<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Filter;

use Nette;
use Nette\SmartObject;
use Ublaboo\DataGrid\DataGrid;

/**
 * @method void addToFormContainer(Nette\Forms\Container $container)
 */
abstract class Filter
{

	use SmartObject;

	/**
	 * @var mixed
	 */
	protected $value;

	/**
	 * @var bool
	 */
	protected $value_set = false;

	/**
	 * @var callable
	 */
	protected $condition_callback;

	/**
	 * @var string
	 */
	protected $key;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string|array
	 */
	protected $column;

	/**
	 * @var string
	 */
	protected $template;

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @var DataGrid
	 */
	protected $grid;

	/**
	 * @var array
	 */
	protected $attributes = [
		'class' => ['form-control', 'input-sm'],
	];

	/**
	 * @var string
	 */
	private $placeholder;


	/**
	 * @param DataGrid     $grid
	 * @param string       $key
	 * @param string       $name
	 * @param string|array $column
	 */
	public function __construct($grid, $key, $name, $column)
	{
		$this->grid = $grid;
		$this->key = $key;
		$this->name = $name;
		$this->column = $column;
	}


	/**
	 * Get filter key
	 * @return mixed
	 */
	public function getKey()
	{
		return $this->key;
	}


	/**
	 * Get filter name
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * Get filter column
	 * @return string
	 */
	public function getColumn()
	{
		return $this->column;
	}


	/**
	 * Tell whether value has been set in this fitler
	 * @return boolean
	 */
	public function isValueSet()
	{
		return $this->value_set;
	}


	/**
	 * Set filter value
	 * @param mixed $value
	 * @return static
	 */
	public function setValue($value)
	{
		$this->value = $value;
		$this->value_set = true;

		return $this;
	}


	/**
	 * Get filter value
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}


	/**
	 * Set html attr placeholder
	 * @param  string $placeholder
	 * @return static
	 */
	public function setPlaceholder($placeholder)
	{
		$this->placeholder = $placeholder;

		return $this;
	}


	/**
	 * Get html attr placeholder
	 * @return string
	 */
	public function getPlaceholder()
	{
		return $this->placeholder;
	}


	/**
	 * Set custom condition on filter
	 * @param  callable $condition_callback
	 * @return static
	 */
	public function setCondition($condition_callback)
	{
		$this->condition_callback = $condition_callback;

		return $this;
	}


	/**
	 * Get filter condition
	 * @return array
	 */
	public function getCondition()
	{
		return [$this->column => $this->getValue()];
	}


	/**
	 * Tell whether custom condition_callback on filter is set
	 * @return bool
	 */
	public function hasConditionCallback()
	{
		return (bool) $this->condition_callback;
	}


	/**
	 * Get custom filter condition
	 * @return callable
	 */
	public function getConditionCallback()
	{
		return $this->condition_callback;
	}


	/**
	 * Filter may have its own template
	 * @param  string $template
	 * @return static
	 */
	public function setTemplate($template)
	{
		$this->template = (string) $template;

		return $this;
	}


	/**
	 * Get filter template path
	 * @return string
	 */
	public function getTemplate()
	{
		return $this->template;
	}


	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}


	/**
	 * @param string $name
	 * @param mixed $value
	 * @return static
	 */
	public function addAttribute($name, $value)
	{
		$this->attributes[$name][] = $value;

		return $this;
	}


	/**
	 * @param string $name
	 * @param mixed $value
	 * @return static
	 */
	public function setAttribute($name, $value)
	{
		$this->attributes[$name] = (array) $value;

		return $this;
	}


	/**
	 * @return array
	 * @deprecated use getAttributes instead
	 */
	public function getAttribtues()
	{
		@trigger_error('getAttribtues is deprecated, use getAttributes instead', E_USER_DEPRECATED);
		return $this->getAttributes();
	}


	/**
	 * @return array
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}


	/**
	 * @param Nette\Forms\Controls\BaseControl $input
	 * @return Nette\Forms\Controls\BaseControl
	 */
	protected function addAttributes($input)
	{
		if ($this->grid->hasAutoSubmit()) {
			$input->setAttribute('data-autosubmit', true);
		} else {
			$input->setAttribute('data-datagrid-manualsubmit', true);
		}

		foreach ($this->attributes as $key => $value) {
			if (is_array($value)) {
				$value = array_unique($value);
				$value = implode(' ', $value);
			}

			$input->setAttribute($key, $value);
		}

		return $input;
	}
}
