<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Filter;

use Nette;

class FilterDateSelect extends FilterRange implements IFilterDate
{

	/**
	 * @var string
	 */
	protected $template = 'datagrid_filter_dateselect.latte';

	/**
	 * @var array
	 */
	protected $format = ['j. n. Y', 'd. m. yyyy'];

	/**
	 * @var string
	 */
	protected $type = 'date-select';

	protected $options = [];


	public function __construct($grid, $key, $name, $column, $options)
	{
		parent::__construct($grid, $key, $name, $column,"-");

		$this->options = $options;
	}

	public function getCondition() {
		$date = $this->getValue();
		$start = new Nette\Utils\DateTime($date["date"]);
		$end = $start->modifyClone("last day of this month");

		return [
			$this->column => [
				"from" => $start->format("Y-m-d"),
				"to" => $end->format("Y-m-d")
			]
		];
	}

	/**
	 * Adds select box to filter form
	 * @param Nette\Forms\Container $container
	 */
	public function addToFormContainer(Nette\Forms\Container $container)
	{
		$container = $container->addContainer($this->key);
		$container->addSelect("date",$this->name,$this->options)->setPrompt("-- vyberte --");
		$this->addAttributes($container["date"]);
		if ($this->grid->hasAutoSubmit()) {
			$container['date']->setAttribute('data-autosubmit-change', true);
		}
	}


	/**
	 * Set format for datepicker etc
	 * @param  string $php_format
	 * @param  string $js_format
	 * @return static
	 */
	public function setFormat($php_format, $js_format)
	{
		$this->format = [$php_format, $js_format];

		return $this;
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
