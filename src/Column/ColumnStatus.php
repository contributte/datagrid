<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Column;

use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridColumnStatusException;
use Ublaboo\DataGrid\Row;
use Ublaboo\DataGrid\Status\Option;
use Ublaboo\DataGrid\Traits;

class ColumnStatus extends Column
{
	use Traits\TButtonCaret;

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
	 * Get particular option
	 * @param  mixed $value
	 * @return Option
	 * @throws DataGridColumnStatusException
	 */
	public function getOption($value)
	{
		foreach ($this->options as $option) {
			if ($option->getValue() === $value) {
				return $option;
			}
		}

		throw new DataGridColumnStatusException("Option [$value] does not exist");
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

		return null;
	}


	/**
	 * Add option to status select
	 * @param mixed $value
	 * @param string $text
	 * @return Option
	 * @throws DataGridColumnStatusException
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
	 * Set all options at once
	 * @param array $options
	 * @return static
	 */
	public function setOptions(array $options)
	{
		foreach ($options as $value => $text) {
			$this->addOption($value, $text);
		}

		return $this;
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
			'column' => $this->getColumn(),
			'key' => $this->getKey(),
			'status' => $this,
		]);
	}
	
	public function setReplacement(array $replacements)	
	{
		throw new DataGridColumnStatusException('Cannot set replacement for Column Status. For status texts replacement use ->setOptions($replacements)');
	}
}
