<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Filter;

use Nette;

class FilterSelect extends Filter
{

	/**
	 * @var array
	 */
	private $options;

	/**
	 * @var string
	 */
	protected $template = 'datagrid_filter_select.latte';


	/**
	 * @param string $key
	 * @param string $name
	 * @param string $options
	 * @param string $column
	 */
	public function __construct($key, $name, array $options, $column)
	{
		parent::__construct($key, $name, $column);

		$this->options = $options;
	}


	/**
	 * Adds select box to filter form
	 * @param Nette\Application\UI\Form $form
	 */
	public function addToFormContainer($form)
	{
		$form->addSelect($this->key, $this->name, $this->options);
	}


	/**
	 * Get filter condition
	 * @return array
	 */
	public function getCondition()
	{
		return [$this->column => $this->getValue()];
	}

}
