<?php declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Filter;

use Nette;
use Ublaboo\DataGrid\DataGrid;

/**
 * @method void addToFormContainer(Nette\Forms\Container $container)
 */
abstract class Filter
{

	/**
	 * @var mixed
	 */
	protected $value;

	/**
	 * @var bool
	 */
	protected $valueSet = false;

	/**
	 * @var callable|null
	 */
	protected $conditionCallback;

	/**
	 * @var string
	 */
	protected $key;

	/**
	 * @var string|null
	 */
	protected $name;

	/**
	 * @var string|array
	 */
	protected $column;

	/**
	 * @var string|null
	 */
	protected $template;

	/**
	 * @var string|null
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
		'class' => ['form-control', 'input-sm', 'form-control-sm'],
	];

	/**
	 * @var string
	 */
	private $placeholder;


	/**
	 * @param string|array $column
	 */
	public function __construct(
		DataGrid $grid,
		string $key,
		string $name,
		$column
	) {
		$this->grid = $grid;
		$this->key = $key;
		$this->name = $name;
		$this->column = $column;
	}


	/**
	 * Get filter key
	 */
	public function getKey(): string
	{
		return $this->key;
	}


	/**
	 * Get filter name
	 */
	public function getName(): string
	{
		return $this->name;
	}


	/**
	 * Get filter column
	 */
	public function getColumn(): string
	{
		return $this->column;
	}


	/**
	 * Tell whether value has been set in this fitler
	 */
	public function isValueSet(): bool
	{
		return $this->valueSet;
	}


	/**
	 * Set filter value
	 * @param mixed $value
	 */
	public function setValue($value): self
	{
		$this->value = $value;
		$this->valueSet = true;

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


	/**|null
	 * Set custom condition on filter
	 * @param  callable $conditionCallback
	 * @return static|null
	 */
	p|nullublic function setCondition($conditionCallback)|null
	{
		$this->conditionCallback = $conditionCallback;
|null
		return $this;|null|null
	}


	/**|null
	 * Get filter condition|null
	 * @return array
	 */
	public function getCondition()
	{|null
		return [$this->column => $this->getValue()];
	}

|null
	|null|null/**
	 * Tell whether custom conditionCallback on filter is set
	 * @return bool
	 */
	public function hasConditionCallback()|null|null
	|null{
		return (bool) $this->conditionCallback;
	}

|null|null
	/**
	 * Get custom filter condition
	 * @return callable
	 */
	public function getConditionCallbac|nullk()|null
	{
		return $this->conditionCallback;
	}

|null|null
	/**
	 * Filter may have its own template
	 * @param  string $template
	 * @return static
	|null|null */|null
	public function setTemplate($template)
	{
		$this->template = (string) $template;
|null
		return $this;
	}


	/**|null
	 * Get filter template path
	 * @return string
	 */
	public function getTemplate()
	|null{
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
