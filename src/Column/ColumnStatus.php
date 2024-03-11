<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Column;

use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Exception\DatagridColumnStatusException;
use Contributte\Datagrid\Row;
use Contributte\Datagrid\Status\Option;
use Contributte\Datagrid\Traits\TButtonCaret;
use Contributte\Datagrid\Traits\TRenderCondition;
use Nette\SmartObject;

/**
 * @method onChange(string $id, string $value)
 */
class ColumnStatus extends Column
{

	use TButtonCaret;
	use SmartObject;
	use TRenderCondition;

	/** @var array|callable[] */
	public array $onChange = [];

	protected string $key;

	protected array $options = [];

	public function __construct(
		Datagrid $grid,
		string $key,
		string $column,
		string $name
	)
	{
		parent::__construct($grid, $key, $column, $name);

		$this->key = $key;

		$this->setTemplate(__DIR__ . '/../templates/column_status.latte');
	}

	public function getKey(): string
	{
		return $this->key;
	}

	public function getOptions(): array
	{
		return $this->options;
	}

	/**
	 * @throws DatagridColumnStatusException
	 */
	public function getOption(mixed $value): Option
	{
		foreach ($this->options as $option) {
			if ($option->getValue() === $value) {
				return $option;
			}
		}

		throw new DatagridColumnStatusException(sprintf('Option [%s] does not exist', $value));
	}

	public function getColumn(): string
	{
		return $this->column;
	}

	/**
	 * Find selected option for current item/row
	 */
	public function getCurrentOption(Row $row): ?Option
	{
		foreach ($this->getOptions() as $option) {
			if ($option->getValue() === $row->getValue($this->getColumn())) {
				return $option;
			}
		}

		return null;
	}

	/**
	 * @throws DatagridColumnStatusException
	 */
	public function addOption(mixed $value, string $text): Option
	{
		if (!is_scalar($value)) {
			throw new DatagridColumnStatusException('Option value has to be scalar');
		}

		$option = new Option($this->grid, $this, $value, $text);

		$this->options[] = $option;

		return $option;
	}

	/**
	 * Set all options at once
	 *
	 * @param string[] $options
	 * @return static
	 */
	public function setOptions(array $options): self
	{
		foreach ($options as $value => $text) {
			$this->addOption($value, $text);
		}

		return $this;
	}

	public function removeOption(mixed $value): void
	{
		foreach ($this->options as $key => $option) {
			if ($option->getValue() === $value) {
				unset($this->options[$key]);
			}
		}
	}

	/**
	 * Column can have variables that will be passed to custom template scope
	 */
	public function getTemplateVariables(): array
	{
		return array_merge($this->templateVariables, [
			'options' => $this->getOptions(),
			'column' => $this->getColumn(),
			'key' => $this->getKey(),
			'status' => $this,
		]);
	}

	public function setReplacement(array $replacements): Column
	{
		throw new DatagridColumnStatusException(
			'Cannot set replacement for Column Status. For status texts replacement use ->setOptions($replacements)'
		);
	}

}
