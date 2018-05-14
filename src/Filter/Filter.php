<?php declare(strict_types = 1);

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
		'class' => ['form-control', 'input-sm', 'form-control-sm'],
	];

	/**
	 * @var string
	 */
	private $placeholder;

	/**
	 * @param string|array $column
	 */
	public function __construct(DataGrid $grid, string $key, string $name, $column)
	{
		$this->grid = $grid;
		$this->key = $key;
		$this->name = $name;
		$this->column = $column;
	}


	/**
	 * Get filter key
	 *
	 * @return mixed
	 */
	public function getKey()
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
		return $this->value_set;
	}


	/**
	 * Set filter value
	 *
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
	 *
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}


	/**
	 * Set html attr placeholder
	 *
	 * @return static
	 */
	public function setPlaceholder(string $placeholder)
	{
		$this->placeholder = $placeholder;

		return $this;
	}


	/**
	 * Get html attr placeholder
	 */
	public function getPlaceholder(): string
	{
		return $this->placeholder;
	}


	/**
	 * Set custom condition on filter
	 *
	 * @return static
	 */
	public function setCondition(callable $condition_callback)
	{
		$this->condition_callback = $condition_callback;

		return $this;
	}


	/**
	 * Get filter condition
	 *
	 * @return array
	 */
	public function getCondition(): array
	{
		return [$this->column => $this->getValue()];
	}


	/**
	 * Tell whether custom condition_callback on filter is set
	 */
	public function hasConditionCallback(): bool
	{
		return (bool) $this->condition_callback;
	}


	/**
	 * Get custom filter condition
	 */
	public function getConditionCallback(): callable
	{
		return $this->condition_callback;
	}


	/**
	 * Filter may have its own template
	 *
	 * @return static
	 */
	public function setTemplate(string $template)
	{
		$this->template = (string) $template;

		return $this;
	}


	/**
	 * Get filter template path
	 */
	public function getTemplate(): string
	{
		return $this->template;
	}


	public function getType(): string
	{
		return $this->type;
	}


	/**
	 * @param mixed $value
	 * @return static
	 */
	public function addAttribute(string $name, $value)
	{
		$this->attributes[$name][] = $value;

		return $this;
	}


	/**
	 * @param mixed $value
	 * @return static
	 */
	public function setAttribute(string $name, $value)
	{
		$this->attributes[$name] = (array) $value;

		return $this;
	}


	/**
	 * @return array
	 * @deprecated use getAttributes instead
	 */
	public function getAttribtues(): array
	{
		@trigger_error('getAttribtues is deprecated, use getAttributes instead', E_USER_DEPRECATED);
		return $this->getAttributes();
	}


	/**
	 * @return array
	 */
	public function getAttributes(): array
	{
		return $this->attributes;
	}


	protected function addAttributes(Nette\Forms\Controls\BaseControl $input): Nette\Forms\Controls\BaseControl
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
