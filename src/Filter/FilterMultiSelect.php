<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Filter;

use Nette;
use Ublaboo\DataGrid\DataGrid;

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
		'class' => ['form-control', 'input-sm', 'selectpicker'],
		'data-selected-text-format' => ['count'],
	];


	/**
	 * @param DataGrid $grid
	 * @param string   $key
	 * @param string   $name
	 * @param string   $options
	 * @param string   $column
	 */
	public function __construct($grid, $key, $name, array $options, $column)
	{
		parent::__construct($grid, $key, $name, $options, $column);

		$this->addAttribute('data-selected-icon-check', DataGrid::$icon_prefix . 'check');
	}


	/**
	 * Get filter condition
	 * @return array
	 */
	public function getCondition()
	{
		$return = [$this->column => []];

		foreach ($this->getValue() as $value) {
			$return[$this->column][] = $value;
		}

		return $return;
	}


	/**
	 * @param Nette\Forms\Container $container
	 * @param string                $key
	 * @param string                $name
	 * @param array                $options
	 * @return Nette\Forms\Controls\SelectBox
	 */
	protected function addControl(Nette\Forms\Container $container, $key, $name, $options)
	{
		/**
		 * Set some translated texts
		 */
		$form = $container->lookup('Nette\Application\UI\Form');
		$t = [$form->getTranslator(), 'translate'];

		$this->addAttribute('title', $t('ublaboo_datagrid.multiselect_choose'));
		$this->addAttribute('data-i18n-selected', $t('ublaboo_datagrid.multiselect_selected'));

		/**
		 * Add input to container
		 */
		$input = $container->addMultiSelect($key, $name, $options);

		return $this->addAttributes($input);
	}
}
