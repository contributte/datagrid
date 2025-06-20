<?php

declare(strict_types=1);

namespace Contributte\Datagrid\Storage;

use Nette\Http\SessionSection;

/**
 * SessionGridStateStorage is a storage for grid state that uses session to persist data.
 * It implements the IGridStateStorage interface.
 */
class SessionStateStorage implements IStateStorage
{

    public function __construct(
        private SessionSection $sessionSection)
    {
    }

    public function loadState(string $key): mixed
    {
        return $this->sessionSection->get($key);
    }

    public function saveState(string $key, mixed $value): void
    {
        $this->sessionSection->set($key, $value);
    }

    public function deleteState(string $key): void
    {
        $this->sessionSection->remove($key);
    }
}
