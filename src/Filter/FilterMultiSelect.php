<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Filter;

use Contributte\Datagrid\Datagrid;
use Nette\Application\UI\Form;
use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;
use UnexpectedValueException;

class FilterMultiSelect extends FilterSelect
{

	protected ?string $type = 'multi-select';

	protected array $attributes = [
		'class' => ['form-select', 'selectpicker', 'form-select-sm'],
		'data-selected-text-format' => ['count'],
	];

	public function __construct(
		Datagrid $grid,
		string $key,
		string $name,
		array $options,
		string $column
	)
	{
		parent::__construct($grid, $key, $name, $options, $column);

		$this->addAttribute('data-selected-icon-check', Datagrid::$iconPrefix . 'check');
	}

	/**
	 * Get filter condition
	 */
	public function getCondition(): array
	{
		$return = [$this->column => []];

		foreach ($this->getValue() as $value) {
			$return[$this->column][] = $value;
		}

		return $return;
	}

	protected function addControl(
		Container $container,
		string $key,
		string $name,
		array $options
	): BaseControl
	{
		/**
		 * Set some translated texts
		 */
		$form = $container->lookup(Form::class);

		if (!$form instanceof Form) {
			throw new UnexpectedValueException();
		}

		$translator = $form->getTranslator();

		if ($translator === null) {
			throw new UnexpectedValueException();
		}

		$this->addAttribute(
			'title',
			$translator->translate('contributte_datagrid.multiselect_choose')
		);
		$this->addAttribute(
			'data-i18n-selected',
			$translator->translate('contributte_datagrid.multiselect_selected')
		);

		/**
		 * Add input to container
		 */
		$input = $container->addMultiSelect($key, $name, $options);

		$this->addAttributes($input);

		return $input;
	}

}
