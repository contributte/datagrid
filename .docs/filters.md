Table of contents

- [Filters](#filters)
    - [Api](#api)
        - [Placeholder](#placeholder)
        - [Custom where condition](#custom-where-condition)
        - [Templates:](#templates)
        - [Filter blocks](#filter-blocks)
        - [Filter type blocks](#filter-type-blocks)
        - [Removing filter](#removing-filter)
    - [FilterText](#filtertext)
    - [FilterSelect](#filterselect)
    - [FilterMultiSelect](#filtermultiselect)
    - [FilterDate](#filterdate)
    - [FilterRange](#filterrange)
    - [FilterDateRange](#filterdaterange)
    - [Default filter values](#default-filter-values)
        - [Resetting filter to default values](#resetting-filter-to-default-values)
    - [Filters rendering](#filters-rendering)
    - [Outer filters rendering](#outer-filters-rendering)
    - [Session - remeber state](#session---remeber-state)
    - [Session - filters / filter values changed](#session---filters--filter-values-changed)
    - [URL refreshing - history API](#url-refreshing---history-api)
    - [Auto submit](#auto-submit)

# Filters

## Api

Either you can add filter to existing column by defining column and filter separately:

```php
$grid->addColumnText('name', 'Name');
$grid->addFilterText('name', 'Name');
```

Or you can add a filter directly to column definition:

```php
$grid->addColumnText('name', 'Name')
	->setFilterText();
```

There are several filter classes and they all have some common behaviour and properties. Let's start with parameters, I will take a `FiterText` as an example.

```php
/**
 * $key, $name, $columns
 */
$grid->addFilterText('name', 'Name');

/**
 * Equivalent
 */
$grid->addFilterText('name', 'Name', 'name');

/**
 * Same functionality - search in column 'name'
 */
$grid->addFilterText('x_foo', 'Name', 'name');
```

`FilterText` is a little bit different than other filters. It can search in multiple columns. Here's how you do that:

```php
$grid->addFilterText('name', 'Search', ['name', 'surname', 'company', 'address']);
```

### Placeholder

```php
$grid->addFilterText('all', 'Search:', ['name', 'id'])
	->setPlaceholder('Search...');
```

### Custom where condition

```php
$grid->addFilterText('custom', 'Custom search:', 'name')
	->setCondition(function(Dibi\Fluent $fluent, $value) {
		/**
		 * The data source is here DibiFluent
		 * No matter what data source you are using,
		 * prepared data source will be passed as the first parameter of your callback function
		 *
		 * If you are using `NextrasDataSource` (DbalCollection),
		 * functions as `findBy` don't work. You need to use `$collection->getQueryBuilder->...` syntax.
		 * See https://github.com/contributte/datagrid/pull/298 for detailed information.
		 */
		$fluent->where('id > ?', strlen($value));
	});
```

### Templates:

Filters can also have their own templates:

```php
$grid->addFilterText('name', 'Name:')
	->setTemplate(__DIR__ . '/templates/filter_name.latte');
```

There is how the default FilterText template looks like:

```latte
{**
 * @param Filter                         $filter
 * @param Nette\Forms\Controls\TextInput $input
 *}

<div class="row">
	{label $input class =>; 'col-sm-3 control-label' /}
	<div class="col-sm-9">
		{input $input, class => 'form-control form-control-sm', data-autosubmit => true}
	</div>
</div>

```

### Filter blocks

User can define filter template via `{block}` macro:

```php
$grid->setTemplateFile(__DIR__ . '/my-grid-template.latte');
$grid->addFilterText('name', 'Name:');
```

And the `my-grid-template.latte`:

```latte
{block filter-name}
{input $input}

```

Arguments `$filter` (Filter class instance), `$input` (filter form input) and `$outer` (true or false) are passed to the block. You can inspire yourself with native filter templates, for example text filter template can be found int `vendor/ublaboo/datagrid/src/templates/datagrid_filter_text.latte`.

### Filter type blocks

Macro

### Removing filter

You can remove filter from grid like so:

```php
$grid->addFilterText('foo', 'Name', 'name');
$grid->removeFilter('foo');
```

## FilterText

By default, when you type "foo bar", `FilterText` will split input words into n single phrases (`... OR (<column> LIKE "%foo%") OR (<column> LIKE "%bar%")`). That behaviour can be overridden:

```php
$grid->addFilterText('name', 'Name')
	->setSplitWordsSearch(false);
```

## FilterSelect

`FilterSelect` has one more parameter - options:

```php
$grid->addFilterSelect('status', 'Status:', ['' => 'All', 1 => 'On', 2 => 'Off']);

/**
 * Equivalent
 */
$grid->addFilterSelect('status', 'Status:', ['' => 'All', 1 => 'On', 2 => 'Off'], 'status');
```

Again, you can use custom condition callback, the same in all other filters.

## FilterMultiSelect

Api of `FilterMultiSelect` is the same as of FilterSelect

```php
$grid->addFilterMultiSelect('status', 'Status:', [1 => 'On', 2 => 'Off', 2 => 'Another option']);
```

Keep in mind that `FilterMultiSelect` uses `bootstrap-select` JS library. Read more on [Introduction](index.md).

## FilterDate

```php
$grid->addFilterDate('created', 'User registerd on');
```

This filter also has some special features. First, it shows datepicker. Second, You can set date format. Sadly, JavaScript has different date formatting modifiers, so you have to set them both at once:

```php
/**
 * This is default formatting
 * $php_format, $js_format
 */
$grid->addFilterDate('created', 'User registerd on')
	->setFormat('j. n. Y', 'd. m. yyyy');
```

## FilterRange

This filter renders two inputs: From and To. If you want to set inputs placeholders, you have to set both in an array.

```php
$grid->addFilterRange('price_range', 'Price:', 'price');
```

## FilterDateRange

`FilterDateRange` is similar:

```php
$grid->addFilterDateRange('date_created', 'User registered:');
```

## Default filter values

Datagrid filters can have default filter values. Once user changes the filter, default values are no longer applied to the filter. Example usage:

```php
$grid->setDefaultFilter(['status' => 1, 'name' => 'Joe']);
```

**Notice!** Values of `FilterRange`, `FilterDateRange` and `FilterMultiSelect` must be of type array:

```php
$grid->addFilterMultiSelect('status', 'Status:', [
	0 => 'Offline',
	1 => 'Online',
	2 => 'Standby'
]);

$grid->addFilterRange('age', 'Age');

$grid->setDefaultFilter(['status' => [1], 'age' => ['from' => 18]]);
```

### Resetting filter to default values

By default, once you reset the filter, default fitler values are applied. If you don't want to apply them after resetting the filter, pass false as a second parameter to `Datagrid::setDefaultFilter()`:

```php
$grid->setDefaultFilter('id' => 10, false);
```

## Filters rendering

Note that **if** you are **rendering** filter **in** datagrid table, you have to choose identical keys for column and filter:

```php
$grid->addColumnText('name', 'Name');
$grid->addFilterText('name', 'Name');

/**
 * This filter won't show up, because it has different key name
 */
$grid->addFilterText('search', 'Name', 'name');
```

## Outer filters rendering

You can set outer filters rendering:

```php
$grid->setOuterFilterRendering(); // - that is true. Or $grid->setOuterFilterRendering(false);
```

## Session - remeber state

Grid refreshes its state on several levels. One could be session. It is by default turned on, but can be disabled:

```php
$grid->setRememberState(false); // Or turned on again: $grid->setRememberState(true);
```

If you want to keep hideable columns in session even when remember state is turned off, use second argument:

```php
$grid->setRememberState(false, true);
```

## Session - filters / filter values changed

When you set some filters and user do some filtering, values are stored in session. After that, when filters are changed (maybe some select options are removed, etc.), datagrid would throw an exception, because it can not find particular filters / filter values that are still stored in session. You can suppress those exception:

```php
$grid->setStrictSessionFilterValues(false);
```

## URL refreshing - history API

Second, grid refreshes URL via history API. So when you refresh, there is always current url. That can be also disabled:

```php
$grid->setRefreshUrl(false); // Or enabled again: $grid->setRefreshUrl(true);
```

## Auto submit

Datagrid filter is submitted automatically after keypress (there is of course a little delay). If you want to disable that feature and use customizable submit button instead, use this code:

```php
$grid->setAutoSubmit(false);
```
