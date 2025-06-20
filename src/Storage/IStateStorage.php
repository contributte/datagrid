<?php

declare(strict_types=1);

namespace Contributte\Datagrid\Storage;

interface IStateStorage
{
    public function loadState(string $key): mixed;
    public function saveState(string $key, mixed $value): void;
    public function deleteState(string $key): void;
}
