<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Filter;

use Nette;

class FilterText extends Filter
{

	/**
	 * @var string
	 */
	protected $template = 'datagrid_filter_text.latte';


	/**
	 * Adds text field to filter form
	 * @param Nette\Forms\Container $container
	 */
	public function addToFormContainer($container)
	{
		$container->addText($this->key, $this->name);

		if ($this->getPlaceholder()) {
			$container[$this->key]->setAttribute('placeholder', $this->getPlaceholder());
		}
	}


	/**
	 * Return array of conditions to put in result [column1 => value, column2 => value]
	 * 	If more than one column exists in fitler text,
	 * 	than there is OR clause put betweeen their conditions
	 * Or callback in case of custom condition callback
	 * @return array|callable
	 */
	public function getCondition()
	{
		return array_fill_keys($this->column, $this->getValue());
	}

}
