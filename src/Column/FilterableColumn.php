<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\Column;

use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Filter\FilterDate;
use Ublaboo\DataGrid\Filter\FilterDateRange;
use Ublaboo\DataGrid\Filter\FilterMultiSelect;
use Ublaboo\DataGrid\Filter\FilterRange;
use Ublaboo\DataGrid\Filter\FilterSelect;
use Ublaboo\DataGrid\Filter\FilterText;

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


	public function __construct(
		DataGrid $grid,
		string $key,
		string $column,
		string $name
	)
	{
		$this->grid = $grid;
		$this->key = $key;
		$this->column = $column;
		$this->name = $name;
	}


	/**
	 * @param string|array|null $columns
	 */
	public function setFilterText($columns = null): FilterText
	{
		if ($columns === null) {
			$columns = [$this->column];
		} else {
			$columns = is_string($columns)
				? [$columns]
				: $columns;
		}

		return $this->grid->addFilterText($this->key, $this->name, $columns);
	}


	public function setFilterSelect(
		array $options,
		?string $column = null
	): FilterSelect
	{
		$column = $column ?? $this->column;

		return $this->grid->addFilterSelect($this->key, $this->name, $options, $column);
	}


	public function setFilterMultiSelect(
		array $options,
		?string $column = null
	): FilterMultiSelect
	{
		$column = $column ?? $this->column;

		return $this->grid->addFilterMultiSelect($this->key, $this->name, $options, $column);
	}


	public function setFilterDate(?string $column = null): FilterDate
	{
		$column = $column ?? $this->column;

		return $this->grid->addFilterDate($this->key, $this->name, $column);
	}


	public function setFilterRange(
		?string $column = null,
		string $nameSecond = '-'
	): FilterRange
	{
		$column = $column ?? $this->column;

		return $this->grid->addFilterRange($this->key, $this->name, $column, $nameSecond);
	}


	public function setFilterDateRange(
		?string $column = null,
		string $nameSecond = '-'
	): FilterDateRange
	{
		$column = $column ?? $this->column;

		return $this->grid->addFilterDateRange($this->key, $this->name, $column, $nameSecond);
	}
}
