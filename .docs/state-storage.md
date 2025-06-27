# State storage

[State storage](#state-storage)
- [Built-in storage implementations](#built-in-storage-implementations)
- [Remember state configuration](#remember-state-configuration)
- [Custom state storage](#custom-state-storage)
  - [Example of custom state storage use case](#example-of-custom-state-storage-use-case)


-----

## State storage

Datagrid provides a flexible state storage system that allows you to persist grid state (filters, sorting, column visibility, etc.) using different storage backends. This enables features like state persistence across multiple devices/browsers and enhanced flexibility for distributed environments.

### Built-in storage implementations

Datagrid comes with two built-in storage implementations:

#### SessionStateStorage
The default storage implementation that maintains existing session-based behavior. Used automatically when state persistence is enabled.

#### NoopStateStorage
A no-op implementation that doesn't persist any state. Used automatically when state persistence is disabled.

### Remember state configuration

Grid refreshes its state automatically. State persistence is by default turned on, but can be disabled:

```php
$grid->setRememberState(false); // Or turned on again: $grid->setRememberState(true);
```

If you want to keep hideable columns in storage even when remember state is turned off, use second argument:

```php
$grid->setRememberState(false, true);
```

#### Automatic storage selection

When you don't explicitly set a storage implementation, datagrid automatically selects the appropriate one:

- **SessionStateStorage** - when `rememberState` is enabled OR column hiding is enabled
- **NoopStateStorage** - when both `rememberState` and column hiding are disabled


### Custom state storage

You can implement your own storage backend by implementing the `IStateStorage` interface.

```php
$grid->setStateStorage(new YourCustomStateStorage());
```

This is useful for scenarios like:

- Storing state in database for cross-device persistence
- Using Redis or other cache systems
- Implementing user-specific state storage
- Creating distributed storage solutions

#### Example of custom state storage use case

##### Custom database storage

```php
use Contributte\Datagrid\Storage\IStateStorage;

class DatabaseStateStorage implements IStateStorage
{
    private int $userId;
    private DatabaseConnection $db;

    public function __construct(int $userId, DatabaseConnection $db)
    {
        $this->userId = $userId;
        $this->db = $db;
    }

    public function loadState(string $key): mixed
    {
        $result = $this->db->query(
            'SELECT state_data FROM datagrid_state WHERE user_id = ? AND state_key = ?',
            $this->userId,
            $key
        )->fetch();

        return $result ? unserialize($result['state_data']) : null;
    }

    public function saveState(string $key, mixed $value): void
    {
        $this->db->query(
            'INSERT INTO datagrid_state (user_id, state_key, state_data) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE state_data = VALUES(state_data)',
            $this->userId,
            $key,
            serialize($value)
        );
    }

    public function deleteState(string $key): void
    {
        $this->db->query(
            'DELETE FROM datagrid_state WHERE user_id = ? AND state_key = ?',
            $this->userId,
            $key
        );
    }
}

// Use custom database storage
$grid->setStateStorage(new DatabaseStateStorage($user->getId(), $database));




