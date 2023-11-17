<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Filter;

interface IFilterDate
{

	/**
	 * Get php format for datapicker
	 */
	public function getPhpFormat(): string;

}
