<?php

/**
 * * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
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


	public function __construct($key, $name, array $options, $column)
	{
		parent::__construct($key, $name, $column);

		$this->options = $options;
	}


	/**
	 * Adds select box to filter form
	 * @param Nette\Application\UI\Form $form
	 */
	public function addToForm($form)
	{
		$form->addSelect($this->key, $this->name, $this->options);
	}


	public function getCondition()
	{
		return [$this->column => $this->getValue()];
	}

}
