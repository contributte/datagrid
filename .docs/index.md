Table of contents

- [Installation](#installation)
	- [Composer](#composer)
	- [Features](#features)
	- [Assets](#assets)
		- [CSS:](#css)
		- [JS:](#js)
		- [Spinners](#spinners)

# Installation

## Composer

Download this package using composer:

```
composer require ublaboo/datagrid
```

## Features

- Pagination
- Sorting by columns
- Sortable (reorderable)
- Items per page - configurable
- Acceptable data sources:
	- Doctrine (QueryBuilder)
	- Doctrine (Collection)
	- Nextras (Collection)
	- Dibi (DibiFluent)
	- Dibi (DibiFluent) for MS-SQL
	- Nette\Database (Please see it's documentation [here](https://github.com/contributte/datagrid-nette-database-data-source))
	- Nette\Database\Table
	- Nette\Database\Table (for MS-SQL)
	- Nette\Database\Table (for PostreSQL)
	- Array
	- Elasticsearch (Please see the documentation [here](https://github.com/contributte/datagrid-elasticsearch-data-source))
	- Remote Api
	- Any other class that implements IDataSource
- Columns (custom templates, custom, renderer, replacement, inline editing):
	- ColumnText
	- ColumnNumber
	- ColumnLink
	- ColumnDateTime
	- ColumnStatus
- Filtering: (custom templates, remembering state):
	- FilterText (multiple columns search)
	- FilterSelect
	- FilterRange
	- FilterDateRange
	- FilterDate
	- FilterMultiSelect
- Actions
- Item detail
- Extendable datagrid template
- Group actions (one/two level select)
- Exports (filtered or not-filtered)
- CSV Export ready
- Tree view (children loaded via ajax)
- Localization
- Row conditions
- Hideable columns
- Ajax spinners

DataGrid can do some really useful stuff.

Let's create a datagrid component!
We will demonstrate our examples in Presenters.

```php
use Ublaboo\DataGrid\DataGrid;

class SimplePresenter extends BasePresenter
{

	public function createComponentSimpleGrid($name)
	{
		$grid = new DataGrid($this, $name);

		$grid->setDataSource($this->db->select('*')->from('ublaboo_example'));
		$grid->addColumnText('name', 'Name');
	}

}
```

And that's it. Go check the app. :)

When you don't line pagination, you can disable it:

```php
$grid->setPagination(false);
```

But that would be a long dump - there are about a thousand rows in database. To change the items per page, select options you will do that via:

```php
$grid->setItemsPerPageList([1, 100, 9090, 2]);
```

## Assets

DataGrid needs for its precise functionality some third party scripts and styles. There is a `bower.json` file withing `ublaboo/datagrid` package that describes all js/css dependencies. You can install them with bower command:

```
bower install ublaboo-datagrid
```

Now you can include these assets into your site:

### CSS:

```html
<link rel="stylesheet" type="text/css" href="../bower_components/bootstrap/dist/css/bootstrap.css">


<link rel="stylesheet" type="text/css" href="../bower_components/happy/dist/happy.css">
<link rel="stylesheet" type="text/css" href="../bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css">
<link rel="stylesheet" type="text/css" href="../bower_components/ublaboo-datagrid/assets/datagrid.css">


<link rel="stylesheet" type="text/css" href="../bower_components/ublaboo-datagrid/assets/datagrid-spinners.css">


<link rel="stylesheet" type="text/css" href="../bower_components/bootstrap-select/dist/css/bootstrap-select.css">
```

### JS:

```html
<script src="../bower_components/jquery/dist/jquery.js"></script>
<script src="../bower_components/nette-forms/src/assets/netteForms.js"></script>
<script src="../bower_components/nette.ajax.js/nette.ajax.js"></script>


<script src="../bower_components/happy/dist/happy.js"></script>
<script src="../bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.js"></script>
<script src="../bower_components/jquery-ui-sortable/jquery-ui-sortable.js"></script>
<script src="../bower_components/ublaboo-datagrid/assets/datagrid.js"></script>


<script src="../bower_components/ublaboo-datagrid/assets/datagrid-instant-url-refresh.js"></script>


<script src="../bower_components/ublaboo-datagrid/assets/datagrid-spinners.js"></script>


<script src="../bower_components/bootstrap/dist/js/bootstrap.js"></script>


<script src="../bower_components/bootstrap-select/dist/js/bootstrap-select.js"></script>
```

You will probably want to use some icon font, but that is in your command. On this project website we use font awesome (you can change the icon prefix by setting new value to static property `DataGrid::$iconPrefix = 'fa fa-';`).

Also initializing nette.ajax.js is required:

```html
<script>
	$.nette.init();
</script>
```

### Spinners

As you can see, there is also a `datagrid-spinners.js` script in a datagrid repository. If you include this file within you project layout, there are some actions, that will show spinner/some other animation when waiting for ajax response. Actions, that has somehow animated spinner:

- Group actions (Try in example above and select delete option - I added `sleep(1)` call to that group action)
- Pagination
- Changing items per page
- Toggling item detail - loading the detail for the first time
