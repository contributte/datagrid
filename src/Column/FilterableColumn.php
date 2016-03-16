<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Column;

use Nette\InvalidArgumentException;
use Ublaboo;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Row;
use Ublaboo\DataGrid\Exception\DataGridException;
use Ublaboo\DataGrid\Exception\DataGridColumnRendererException;
use Ublaboo\DataGrid\Exception\DataGridHasToBeAttachedToPresenterComponentException;
use Nette\Utils\Html;

abstract class FilterableColumn
{

	/**
	 * @var string
	 */
	protected $key;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var DataGrid
	 */
	protected $grid;

	/**
	 * @var string
	 */
	protected $column;


	/**
	 * @param DataGrid $grid
	 * @param string   $key
	 * @param string   $column
	 * @param string   $name
	 */
	public function __construct(DataGrid $grid, $key, $column, $name)
	{
		$this->grid = $grid;
		$this->key = $key;
		$this->column = $column;
		$this->name = $name;
	}


	public function setFilterText($columns = NULL)
	{
		if (NULL === $columns) {
			$columns = [$this->column];
		} else {
			$columns = is_string($columns) ? [$columns] : $columns;
		}

		return $this->grid->addFilterText($this->key, $this->name, $columns);
	}


	public function setFilterSelect(array $options, $column = NULL)
	{
		$column = $column === NULL ? $this->column : $column;

		return $this->grid->addFilterSelect($this->key, $this->name, $options, $column);
	}


	public function setFilterDate($column = NULL)
	{
		$column = $column === NULL ? $this->column : $column;

		return $this->grid->addFilterDate($this->key, $this->name, $column);
	}


	public function setFilterRange($column = NULL, $name_second = '-')
	{
		$column = $column === NULL ? $this->column : $column;

		return $this->grid->addFilterRange($this->key, $this->name, $column, $name_second);
	}


	public function setFilterDateRange($column = NULL, $name_second = '-')
	{
		$column = $column === NULL ? $this->column : $column;

		return $this->grid->addFilterDateRange($this->key, $this->name, $column, $name_second);
	}

}
