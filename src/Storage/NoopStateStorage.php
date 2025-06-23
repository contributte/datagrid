<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Storage;

/**
 * A "No-Operation" state storage that does nothing.
 * Useful when persistence is not required.
 */
class NoopStateStorage implements IStateStorage
{

	public function loadState(string $key): mixed
	{
		return null; // Always returns null, as nothing is stored
	}

	public function saveState(string $key, mixed $value): void
	{
		// Do nothing
	}

	public function deleteState(string $key): void
	{
		// Do nothing
	}

}
