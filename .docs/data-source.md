# Datasources

- [ORM Relations](#orm-relations)
- [After loading data](#after-loading-data)
- [ApiDataSource](#apidatasource)
- [NextrasDataSource](#nextrasdatasource)
- [NetteDatabaseTableDataSource](#nettedatabasetabledatasource)

-----

There are these supported datasources so far:

- Doctrine (QueryBuilder)
- Doctrine (Collection)
- Nextras (Collection)
- Dibi (DibiFluent)
- Dibi (DibiFluent) for MS-SQL
- Nette\Database (Please see its documentation [here](https://github.com/contributte/datagrid-nette-database-data-source))
- Nette\Database\Table
- Nette\Database\Table (for MS-SQL)
- Nette\Database\Table (for PostgreSQL)
- Array
- Elasticsearch
- Remote Api
- Any other class that implements IDataSource

You can set data source like this:

```php
$grid->setDataSource($this->ndb->table('user')); // NDBT
$grid->setDataSource($this->dibi->select('*')->from('user')); // Dibi
$grid->setDataSource([['id' => 1, 'name' => 'John'], ['id' => 2, 'name' => 'Joe']]); // Array
$grid->setDataSource($exampleRepository->createQueryBuilder('er')); // Doctrine query builder
# ...
```

The primary key column is by default `id`. You can change that:

```php
$grid->setPrimaryKey('email');
```

Once you have set a data source, you can add columns to the datagrid.

## ORM Relations

When you are using for example Doctrine as a data source, you can easily access another related entities for rendering in column. Let's say you have an entity `User` and each instance can have a property `$name` and `$grandma`. `$grandma` is also an instance of `User` class. Displaying people and their grandmas is very simple then - just use this dot notation:

```php
$grid->addColumnText('name', 'Name', 'name');
$grid->addColumnText('grandma_name', 'Grandma', 'grandma.name');
```

## After loading data

Register a callback on `$grid->getDataModel()->onDataLoaded` to prepare related data in bulk. The event belongs to `DataModel` and runs once after each `filterData()` or `filterRow()` fetch, including empty results. It covers rendering, single-row redraws and exports, before consumers create rows or invoke column renderers.

For example, load item counts for all fetched orders in one query:

```php
$grid->setDataSource($orderRepository->findAll());
$itemCounts = [];

$grid->getDataModel()->onDataLoaded[] = static function (array $items) use ($orderRepository, &$itemCounts): void {
  $ids = array_map(static fn ($order) => $order->id, $items);
  $itemCounts = $ids === [] ? [] : $orderRepository->getItemCountsByOrderIds($ids);
};

$grid->addColumnNumber('itemCount', 'Items')
  ->setRenderer(static function ($order) use (&$itemCounts): int {
    return $itemCounts[$order->id] ?? 0;
  });
```

The event receives the result of the data-loading operation after its filters, sorting and optional pagination have been applied. Exports load their full result set, so the example also prepares values for orders beyond the displayed page.

Both loading methods return the same array passed to the event. Arrays retain their keys; traversables are always materialized using `iterator_to_array()` with key preservation, whether or not subscribers are registered. Standard PHP array key semantics apply, including the last value winning for repeated iterator keys. Custom lazy sources are therefore fully consumed before the event and before rendering or export. The callback is a notification: it does not receive the array by reference and its return value is ignored.

Call `setDataSource()` before `getDataModel()`; accessing an uninitialized data model throws `DatagridException`. Each `setDataSource()` call creates a new model, so register callbacks on the model for that source.

The existing `DoctrineDataSource::$onDataLoaded` remains unchanged for compatibility. For Doctrine sources it runs first, inside `getData()`, followed by the model's event. Register at one layer for each piece of work to avoid doing it twice.

## ApiDataSource

There is also datasource, that takes data from remote api. It is experimental, you can extend it and overwrite whatever you want.

Basic usage:

```php
$grid->setDataSource(
	new Contributte\Datagrid\DataSource\ApiDataSource('http://my.remote.api')
);
```

The idea is simply to forward filtering/sorting/limit/... to remote api. Feel free to leave me a comment if you want to add/improve something.

## NextrasDataSource

There is one specific behaviour when using Nextras ORM. When custom filter conditions are used, user has to work not with given `Collection` instance, but with `Collection::getQueryBuilder()`. That snippet of code will not work correctly, because `DbalCollection` calls clone on each of its methods:

```php
$grid->getFilter('name')
	->setCondition(function ($collection, $value) {
		$collection->limitBy(1);
	});
```

User should use collection's `QueryBuilder` instead:

```php
$grid->getFilter('name')
	->setCondition(function ($collection, $value) {
		$collection->getQueryBuilder()->andWhere('name LIKE %s', "%$value%");
	});
```

## NetteDatabaseTableDataSource

There is a special feature for `NetteDatabaseTableDataSource` and referenced/related columns. When you want to reach related column from another table, you can do that using this syntax:

```php
$grid->addColumnText('name', 'Name', ':related_table.name');
```

For referenced table column, just remove the colon:

```php
$grid->addColumnText('name', 'Name', 'referenced_table.name');
```

In case you want to specify the "through-column", use following syntax:

```php
$grid->addColumnText('name', 'Name', ':related_table.name:through_column_id');
$grid->addColumnText('name', 'Name', 'referenced_table.name:through_column_id');
```

## ElasticDataSource

```php
$grid->setDataSource(
    new ElasticsearchDataSource(
        $client, // Elasticsearch\Client
        'users', // Index name
        'user' // Index type
    )
);
```
