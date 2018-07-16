# Template

## Extending

When you set custom datagrid template, you will probably want to extend it. There are some `blocks` defined, so you can extend just some blocks. Presenter:

```php
$grid->setTemplateFile(__DIR__ . '/../../custom_datagrid_template.latte');
```

Template:

```
{extends $original_template}

{block data}
	{foreach $items as $item}
		{foreach $columns as $key => $column}
			{$column->render($item)}
		{/foreach}
	{/foreach}


{* Another latte code... *}
```

## Column definition

Or you can define column template by defining special block(s):

```
{extends $original_template}

{define col-id}
	:)
{/define}

```

This will overwrite native rendering of ID column (`$grid->addColumn('id', 'Id');`).

## Column header definition

Or you can define column header template:

```
{extends $original_template}

{define col-id-header}
	This is <strong>ID</strong> column
{/define}

```

## Table class definition

By default, table has this class: `table table-hover table-striped table-bordered`. You can change that in `{block #table-class}`:

```
{block table-class}table table-hovertable-condensed table-bordered
```

## Icons definition

<p n:syntax="off">Some icons are also surrounded by `{block icon-*}` macro. You can overwrite these blocks with your icons. The blocks are:

```
{block icon-sort}{/}
{block icon-sort-up}{/}
{block icon-sort-down}{/}
{block icon-caret-down}{/}
{block icon-chevron}{/}
```
