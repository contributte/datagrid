<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Column;

use Ublaboo\DataGrid\Row;
use Ublaboo\DataGrid\Status\Option;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridColumnStatusException;

class ColumnStatus extends Column
{

	/**
	 * @var callable[]
	 */
	public $onChange = [];

	/**
	 * @var string
	 */
	protected $key;

	/**
	 * @var array
	 */
	protected $options = [];

	/**
	 * @var boolean
	 */
	protected $caret = TRUE;


	/**
	 * @param DataGrid $grid
	 * @param string   $key
	 * @param string   $column
	 * @param string   $name
	 */
	public function __construct(DataGrid $grid, $key, $column, $name)
	{
		parent::__construct($grid, $key, $column, $name);

		$this->key = $key;

		$this->setTemplate(__DIR__ . '/../templates/column_status.latte');
	}


	/**
	 * @return string
	 */
	public function getKey()
	{
		return $this->key;
	}


	/**
	 * @return array
	 */
	public function getOptions()
	{
		return $this->options;
	}


	/**
	 * @return string
	 */
	public function getColumn()
	{
		return $this->column;
	}


	/**
	 * Find selected option for current item/row
	 * @param  Row    $row
	 * @return Option|NULL
	 */
	public function getCurrentOption(Row $row)
	{
		foreach ($this->getOptions() as $option) {
			if ($option->getValue() == $row->getValue($this->getColumn())) {
				return $option;
			}
		}

		return NULL;
	}


	/**
	 * Add option to status select
	 * @param mixed $value
	 * @param string $text
	 * @return Option
	 */
	public function addOption($value, $text)
	{
		if (!is_scalar($value)) {
			throw new DataGridColumnStatusException('Option value has to be scalar');
		}

		$option = new Option($this, $value, $text);

		$this->options[] = $option;

		return $option;
	}


	/**
	 * @param  mixed $value
	 * @return void
	 */
	public function removeOption($value)
	{
		foreach ($this->options as $key => $option) {
			if ($option->getValue() == $value) {
				unset($this->options[$key]);
			}
		}
	}


	/**
	 * Column can have variables that will be passed to custom template scope
	 * @return array
	 */
	public function getTemplateVariables()
	{
		return array_merge($this->template_variables, [
			'options' => $this->getOptions(),
			'column'  => $this->getColumn(),
			'key'     => $this->getKey(),
			'status'  => $this
		]);
	}


	/**
	 * Should be a "caret" present in status dropdown?
	 * @param bool $use_caret
	 * @return static
	 */
	public function setCaret($use_caret)
	{
		$this->caret = (bool) $use_caret;

		return $this;
	}


	/**
	 * @return boolean
	 */
	public function hasCaret()
	{
		return $this->caret;
	}

}
