<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Filter;

use Contributte\Datagrid\Datagrid;
use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;

/**
 * @method void addToFormContainer(Container $container)
 */
abstract class Filter
{

	protected mixed $value;

	protected bool $valueSet = false;

	/** @var callable|null */
	protected $conditionCallback;

	protected ?string $template = null;

	protected ?string $type = null;

	protected array $attributes = [
		'class' => ['form-control', 'form-control-sm'],
	];

	private ?string $placeholder = null;

	public function __construct(protected Datagrid $grid, protected string $key, protected string $name)
	{
	}

	abstract public function getCondition(): array;

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
	 * @return static
	 */
	public function setValue(mixed $value): self
	{
		$this->value = $value;
		$this->valueSet = true;

		return $this;
	}

	public function getValue(): mixed
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
	 * @return static
	 */
	public function addAttribute(string $name, mixed $value): self
	{
		$this->attributes[$name][] = $value;

		return $this;
	}

	/**
	 * @return static
	 */
	public function setAttribute(string $name, mixed $value): self
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
			$input->setHtmlAttribute('data-autosubmit', true);
		} else {
			$input->setHtmlAttribute('data-datagrid-manualsubmit', true);
		}

		foreach ($this->attributes as $key => $value) {
			if (is_array($value)) {
				$value = array_unique($value);
				$value = implode(' ', $value);
			}

			$input->setHtmlAttribute($key, $value);
		}

		return $input;
	}

}
