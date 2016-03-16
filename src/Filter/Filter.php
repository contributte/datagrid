<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Filter;

use Nette;

/**
 * @method void addToFormContainer(Nette\Forms\Container $container)
 */
abstract class Filter extends Nette\Object
{

	/**
	 * @var mixed
	 */
	protected $value;

	/**
	 * @var bool
	 */
	protected $value_set = FALSE;

	/**
	 * @var callable
	 */
	protected $condition_callback;

	/**
	 * @var string
	 */
	private $placeholder;

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
	 * @param string $key
	 * @param string $name
	 * @param string|array $column
	 */
	public function __construct($key, $name, $column)
	{
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
	 */
	public function setValue($value)
	{
		$this->value = $value;
		$this->value_set = TRUE;
	}


	/**
	 * Get fitler value
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
	 * @param callable $condition_callback
	 */
	public function setCondition($condition_callback)
	{
		$this->condition_callback = $condition_callback;
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

}
