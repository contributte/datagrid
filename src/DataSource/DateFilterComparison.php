<?php declare(strict_types = 1);

namespace Contributte\Datagrid\DataSource;

trait DateFilterComparison
{

	protected bool $indexFriendlyDateFilter = true;

	/**
	 * Disable for legacy DATE(column) comparisons, e.g. timezone-bearing text.
	 * MSSQL-specific overrides do not use this setting.
	 */
	public function setIndexFriendlyDateFilter(bool $enabled = true): static
	{
		$this->indexFriendlyDateFilter = $enabled;

		return $this;
	}

}
