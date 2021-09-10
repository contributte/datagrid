<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\Filter;

use Nette\Application\UI\Form;
use Nette\Forms\Container;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Json;
use Ublaboo\DataGrid\DataGrid;
use UnexpectedValueException;

class FilterAjaxSearchSelect extends FilterSelect {

	/** @var string  */
	protected $template = 'datagrid_filter_ajax_select.latte';

	/** @var callable */
	protected $selectedCallback;

	/**
	 * @var string
	 */
	protected $type = 'multi-select';

	/**
	 * @var array
	 */
	protected $attributes = [
		'class'                     => [ 'form-control', 'input-sm', 'form-control-sm', "select2-ajax"],
		'data-selected-text-format' => [ 'count' ],
	];

	protected $items = [];


	public function __construct(
		DataGrid $grid,
		string $key,
		string $name,
		string $column,
		string $metadataKey,
		?callable $selectedCallback = null
	) {
		parent::__construct( $grid, $key, $name, [], $column );

		$this->selectedCallback = ($selectedCallback === null) ? function ($selected) {
			return [];
		} : $selectedCallback;

		$this->addAttribute( 'data-selected-icon-check', DataGrid::$iconPrefix . 'check' );
		$this->addAttribute("data-metadata-key",$metadataKey);
	}
	protected function addControl(
		Container $container,
		string $key,
		string $name,
		array $options = []
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
		$input = $container->addMultiSelect($key, $name, [])->checkDefaultValue(false);
		if(!empty($this->items)){
			$input->setItems($this->items);
		}
		$this->addAttributes($input);

		return $input;
	}

	/**
	 * @param array $items
	 */
	public function setItems( array $items ): self {
		$this->items = $items;
		return $this;
	}

	/**
	 * @return callable
	 */
	public function getSelectedCallback():callable {
		return $this->selectedCallback;
	}
}