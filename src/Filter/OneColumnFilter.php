<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Filter;

use Contributte\Datagrid\Datagrid;

abstract class OneColumnFilter extends Filter
{

	public function __construct(
		Datagrid $datagrid,
		string $key,
		string $name,
		protected string $column
	)
	{
		parent::__construct($datagrid, $key, $name);
	}

	/**
	 * Get filter column
	 */
	public function getColumn(): string
	{
		return $this->column;
	}

	public function getCondition(): array
	{
		return [$this->column => $this->getValue()];
	}

}
