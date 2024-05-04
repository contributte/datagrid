Table of contents

- [Pagination](#pagination)
	- [Custom template](#custom-template)

# Pagination

You can **disable** pagination, if you don't want to use it:

```php
$datagrid->setPagination(false);
```

You can set custom items count per page:

```php
$datagrid->setItemsPerPageList([10, 20, 50, 100, 200, 500]);
```

You can set default items per page:

```php
$datagrid->setDefaultPerPage(50);
```

## Custom template

You can set custom template for pagination:

```php
$datagrid->setCustomPaginatorTemplate(__DIR__ . '/templates/datagrid/pagination.latte');
```

See `DatagridPaginator/templates/data_grid_paginator.latte` for default template as an example.
