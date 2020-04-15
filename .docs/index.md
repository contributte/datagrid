Table of contents

- [Installation](#installation)
	- [Composer](#composer)
	- [Features](#features)
	- [Assets](#assets)

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

DataGrid needs for its precise functionality some third party scripts and styles. Install all required assets with NPM.

```
npm install --save ublaboo-datagrid
```

**CSS (external)**

- bootstrap
- bootstrap datepicker
- bootstrap select

**CSS**

- datagrid.css
- datagrid-spinners.css

**JS (external)**

- jquery
- nette forms
- nette ajax / naja
- bootstrap
- bootstrap datepicker
- bootstrap select

**JS**

- datagrid.js
- datagrid-instant-url-refresh.js
- datagrid-spinners.js

**Icons**

You will probably want to use some icon font, but that is in your command.
On this project website we use font awesome (you can change the icon prefix by setting new value to static property `DataGrid::$iconPrefix = 'fa fa-';`).

**Spinners**

As you can see, there is also a `datagrid-spinners.js` script in a datagrid repository. If you include this file within you project layout, there are some actions, that will show spinner/some other animation when waiting for ajax response. Actions, that has somehow animated spinner:

- Group actions
- Pagination
- Changing items per page
- Toggling item detail - loading the detail for the first time
