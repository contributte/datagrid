<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Column;

use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Filter\FilterDate;
use Contributte\Datagrid\Filter\FilterDateRange;
use Contributte\Datagrid\Filter\FilterMultiSelect;
use Contributte\Datagrid\Filter\FilterRange;
use Contributte\Datagrid\Filter\FilterSelect;
use Contributte\Datagrid\Filter\FilterText;

abstract class FilterableColumn
{

	public function __construct(protected Datagrid $grid, protected string $key, protected string $column, protected string $name)
	{
	}

	public function setFilterText(string|array|null $columns = null): FilterText
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
		$column ??= $this->column;

		return $this->grid->addFilterSelect($this->key, $this->name, $options, $column);
	}

	public function setFilterMultiSelect(
		array $options,
		?string $column = null
	): FilterMultiSelect
	{
		$column ??= $this->column;

		return $this->grid->addFilterMultiSelect($this->key, $this->name, $options, $column);
	}

	public function setFilterDate(?string $column = null): FilterDate
	{
		$column ??= $this->column;

		return $this->grid->addFilterDate($this->key, $this->name, $column);
	}

	public function setFilterRange(
		?string $column = null,
		string $nameSecond = '-'
	): FilterRange
	{
		$column ??= $this->column;

		return $this->grid->addFilterRange($this->key, $this->name, $column, $nameSecond);
	}

	public function setFilterDateRange(
		?string $column = null,
		string $nameSecond = '-'
	): FilterDateRange
	{
		$column ??= $this->column;

		return $this->grid->addFilterDateRange($this->key, $this->name, $column, $nameSecond);
	}

}
