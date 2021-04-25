<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\Filter;

use Nette;
use Nette\Forms\Controls\BaseControl;
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
	 * @var string
	 */
	protected $name;

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
	 * @var string|null
	 */
	private $placeholder;


	abstract public function getCondition(): array;


	public function __construct(
		DataGrid $grid,
		string $key,
		string $name
	)
	{
		$this->grid = $grid;
		$this->key = $key;
		$this->name = $name;
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
	 * Tell whether value has been set in this fitler
	 */
	public function isValueSet(): bool
	{
		return $this->valueSet;
	}


	/**
	 * @param mixed $value
	 * @return static
	 */
	public function setValue($value): self
	{
		$this->value = $value;
		$this->valueSet = true;

		return $this;
	}


	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}


	/**
	 * Set HTML attribute "placeholder"
	 *
	 * @return static
	 */
	public function setPlaceholder(string $placeholder): self
	{
		$this->placeholder = $placeholder;

		return $this;
	}


	public function getPlaceholder(): ?string
	{
		return $this->placeholder;
	}


	/**
	 * Set custom condition on filter
	 *
	 * @return static
	 */
	public function setCondition(callable $conditionCallback): self
	{
		$this->conditionCallback = $conditionCallback;

		return $this;
	}


	public function getConditionCallback(): ?callable
	{
		return $this->conditionCallback;
	}


	/**
	 * @return static
	 */
	public function setTemplate(string $template): self
	{
		$this->template = $template;

		return $this;
	}


	public function getTemplate(): ?string
	{
		return $this->template;
	}


	public function getType(): ?string
	{
		return $this->type;
	}


	/**
	 * @param mixed $value
	 * @return static
	 */
	public function addAttribute(string $name, $value): self
	{
		$this->attributes[$name][] = $value;

		return $this;
	}


	/**
	 * @param mixed $value
	 * @return static
	 */
	public function setAttribute(string $name, $value): self
	{
		$this->attributes[$name] = (array) $value;

		return $this;
	}


	public function getAttributes(): array
	{
		return $this->attributes;
	}


	protected function addAttributes(BaseControl $input): BaseControl
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
