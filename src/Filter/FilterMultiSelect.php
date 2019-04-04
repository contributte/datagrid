<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\Filter;

use Nette\Application\UI\Form;
use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;
use Ublaboo\DataGrid\DataGrid;
use UnexpectedValueException;

class FilterMultiSelect extends FilterSelect
{

	/**
	 * @var string
	 */
	protected $type = 'multi-select';

	/**
	 * @var array
	 */
	protected $attributes = [
		'class' => ['form-control', 'input-sm', 'selectpicker', 'form-control-sm'],
		'data-selected-text-format' => ['count'],
	];


	public function __construct(
		DataGrid $grid,
		string $key,
		string $name,
		array $options,
		string $column
	)
	{
		parent::__construct($grid, $key, $name, $options, $column);

		$this->addAttribute('data-selected-icon-check', DataGrid::$iconPrefix . 'check');
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
		$form = $container->lookup('Nette\Application\UI\Form');

		if (!$form instanceof Form) {
			throw new UnexpectedValueException();
		}

		$translator = $form->getTranslator();

		if ($translator === null) {
			throw new UnexpectedValueException();
		}

		$this->addAttribute(
			'title',
			$translator->translate('ublaboo_datagrid.multiselect_choose')
		);
		$this->addAttribute(
			'data-i18n-selected',
			$translator->translate('ublaboo_datagrid.multiselect_selected')
		);

		/**
		 * Add input to container
		 */
		$input = $container->addMultiSelect($key, $name, $options);

		$this->addAttributes($input);

		return $input;
	}
}
