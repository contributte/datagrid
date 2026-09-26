# Events

- [Datagrid events](#datagrid-events)
- [DataModel events](#datamodel-events)
- [Batch loading related data](#batch-loading-related-data)

-----

Datagrid and its `DataModel` expose events as public arrays of callbacks. Register a listener by appending to the array:

```php
use Contributte\Datagrid\Datagrid;

$grid->onRender[] = function (Datagrid $grid): void {
	// ...
};
```

## Datagrid events

| Event | Arguments | When it is invoked |
| --- | --- | --- |
| `onRender` | `Datagrid $grid` | At the start of `render()`, before data is fetched. |
| `onRedraw` | None | When a redraw is requested, for example by AJAX filtering, sorting, paging, reloading, column visibility changes or `redrawItem()`. |
| `onExport` | `Datagrid $grid` | At the start of `handleExport()`, before export data is fetched. |
| `onColumnAdd` | `string $key, Column $column` | After a column has been added to the grid. |
| `onColumnShow` | `string $key` | After a column has been shown by the user. |
| `onColumnHide` | `string $key` | After a column has been hidden by the user. |
| `onShowDefaultColumns` | None | After the user resets column visibility to defaults. |
| `onShowAllColumns` | None | After the user shows all columns. |
| `onFiltersAssembled` | `Filter[] $filters` | After filters have been assembled, before they are applied to the data source. |

## DataModel events

The data model is created by `setDataSource()` and is available via `$grid->getDataModel()`. Register listeners after setting the data source; setting it again replaces the model and its listeners.

| Event | Arguments | When it is invoked |
| --- | --- | --- |
| `onBeforeFilter` | `IDataSource $dataSource` | Before filtering in `filterData()` or `filterRow()`. |
| `onAfterFilter` | `IDataSource $dataSource` | After `filter()` in `filterData()`. In `filterRow()`, it runs before `filterOne()`. |
| `onAfterPaginated` | `IDataSource $dataSource` | After sorting and limit/offset have been applied (only when pagination is enabled). |
| `onDataLoaded` | `array $items` | After each fetch, including empty results, before rows are created. Covers rendering, single-row redraws and exports. |

`DoctrineDataSource` also has its own `onDataLoaded` event which fires inside `getData()`, before the data model event. Subscribe at only one layer for each piece of work.

## Batch loading related data

Column renderers sometimes need related data for the whole result. Loading it per row causes N+1 queries. Use `onDataLoaded` to load it in one query for all fetched items, including exported ones:

```php
$grid->setDataSource($orderRepository->findAll());
$itemCounts = [];

$grid->getDataModel()->onDataLoaded[] = function (array $orders) use ($orderRepository, &$itemCounts): void {
	$ids = array_map(fn ($order) => $order->id, $orders);
	$itemCounts = $ids === [] ? [] : $orderRepository->getItemCountsByOrderIds($ids);
};

$grid->addColumnNumber('itemCount', 'Items')
	->setRenderer(function ($order) use (&$itemCounts): int {
		return $itemCounts[$order->id] ?? 0;
	});
```

Capture `$itemCounts` by reference in both callbacks so the renderer sees the loaded values. Rendering uses the loaded page; exports load their full result set.

The callback receives an array with preserved keys. Traversables are always materialized, even without listeners; duplicate keys retain the last value, and custom lazy sources are fully consumed. This is a notification: the array is not passed by reference and the return value is ignored.
