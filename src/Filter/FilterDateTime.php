<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Filter;

use Nette;

class FilterDateTime extends Filter
{

	/**
	 * @var string
	 */
	protected $template = 'datagrid_filter_datetime.latte';


	/**
	 * Adds select box to filter form
	 * @param Nette\Application\UI\Form $form
	 */
	public function addToFormContainer($form)
	{
		$form->addText($this->key, $this->name)
			->setAttribute('data-datepicker');

		if ($this->getPlaceholder()) {
			$form[$this->key]->setAttribute('placeholder', $this->getPlaceholder());
		}
	}


	public function getCondition()
	{
		return [$this->column => $this->getValue()];
	}

}
