Table of contents

- [Installation](#installation)
	- [Composer](#composer)
	- [Features](#features)

# Installation

## Composer

Download this package using composer:

```bash
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

Datagrid can do some really useful stuff.

Let's create a datagrid component!
We will demonstrate our examples in Presenters.

```php
use Contributte\Datagrid\Datagrid;

class SimplePresenter extends BasePresenter
{

	public function createComponentSimpleGrid($name)
	{
		$grid = new Datagrid($this, $name);

		$grid->setDataSource($this->db->select('*')->from('example'));
		$grid->addColumnText('name', 'Name');
	}

}
```

And that's it. Go check the app. :)
