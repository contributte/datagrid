<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Filter;

use Nette;

class FilterDate extends Filter
{

	/**
	 * @var string
	 */
	protected $template = 'datagrid_filter_date.latte';

	/**
	 * @var array
	 */
	protected $format = ['j. n. Y', 'd. m. yyyy'];


	/**
	 * Adds select box to filter form
	 * @param Nette\Application\UI\Form $form
	 */
	public function addToFormContainer($form)
	{
		$form->addText($this->key, $this->name)
			->setAttribute('data-provide', 'datepicker')
			->setAttribute('data-date-orientation', 'bottom')
			->setAttribute('data-date-format', $this->getJsFormat())
			->setAttribute('data-date-today-highlight', 'true')
			->setAttribute('data-date-autoclose', 'true');

		if ($this->getPlaceholder()) {
			$form[$this->key]->setAttribute('placeholder', $this->getPlaceholder());
		}
	}


	public function getCondition()
	{
		return [$this->column => $this->getValue()];
	}


	public function setFormat($php_format, $js_format)
	{
		$this->format = [$php_format, $js_format];
	}


	public function getPhpFormat()
	{
		return $this->format[0];
	}


	public function getJsFormat()
	{
		return $this->format[1];
	}

}
