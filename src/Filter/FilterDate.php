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
	 * @param Nette\Forms\Container $container
	 */
	public function addToFormContainer($container)
	{
		$container->addText($this->key, $this->name)
			->setAttribute('data-provide', 'datepicker')
			->setAttribute('data-date-orientation', 'bottom')
			->setAttribute('data-date-format', $this->getJsFormat())
			->setAttribute('data-date-today-highlight', 'true')
			->setAttribute('data-date-autoclose', 'true');

		if ($this->getPlaceholder()) {
			$container[$this->key]->setAttribute('placeholder', $this->getPlaceholder());
		}
	}


	/**
	 * Set format for datepicker etc
	 * @param string $php_format
	 * @param string $js_format
	 */
	public function setFormat($php_format, $js_format)
	{
		$this->format = [$php_format, $js_format];
	}


	/**
	 * Get php format for datapicker
	 * @return string
	 */
	public function getPhpFormat()
	{
		return $this->format[0];
	}


	/**
	 * Get js format for datepicker
	 * @return string
	 */
	public function getJsFormat()
	{
		return $this->format[1];
	}

}
