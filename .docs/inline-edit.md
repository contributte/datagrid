Table of contents

- [Inline editing](#inline-editing)
    - [Small inline editing](#small-inline-editing)
        - [Different input types and element attributes](#different-input-types-and-element-attributes)
        - [Render different content then is edited](#render-different-content-then-is-edited)
    - [Big inline editing](#big-inline-editing)
        - [What happens after editing](#what-happens-after-editing)
        - [Show non editing Columns](#show-non-editing-columns)

# Inline editting

## Small inline editing

![Small inline editing](https://github.com/contributte/datagrid/blob/master/.docs/assets/inline_edit.gif?raw=true)

As you can see in the example above (or on the homepage), there is column name and it is editable. You can click on the column cell and a textarea will appear. Then on 'blur', ajax request is sent and your callback is fired.

```php
/**
 * Example callback
 */
$grid->addColumnText('name', 'Name')
	->setSortable()
	->setEditableCallback(function($id, $value): void {
		echo("Id: $id, new value: $value"); die;
	});

/**
 * Or you can do that properly
 */
$grid->addColumnText('name', 'Name')
	->setEditableCallback([$this, 'columnNameEdited']);
```

### Different input types and element attributes

Small inline edit is not limited to textarea, you can you input of type of your choice (that input will be submitted with either **blur** or **submit** (enter press) event). Example usage:

```php
$grid->addColumnText('name', 'Name')
	->setEditableCallback(/**...*/)
	->setEditableInputType('text', ['class' => 'form-control']);
```

Or you can use a select:

```php
$grid->addColumnText('name', 'Name')
	->setEditableCallback(/**...*/)
	->setEditableInputTypeSelect([
		0 => 'Offline',
		1 => 'Online',
		2 => 'Standby'
	]);
```


### Render different content then is edited

![different content](https://github.com/contributte/datagrid/blob/master/.docs/assets/inline_edit_2.gif?raw=true)

As you can see in the demo above, you can edit the link column but actually, only the link text will be edited. That you can achieve by following code:

```php
$grid->addColumnLink('link', 'Link', 'this#demo', 'name', ['id'])
	->setEditableValueCallback(function(Dibi\Row $row): string {
		return $row->name;
	})
	->setEditableCallback(function($id, $value): Html {
		$link = Html::el('a')->href($this->link('this#demo', ['id' => $id]))
			->setText($value);

		return $link; // Important line - right here you have to return new content which will be rendered in edited column
	});
```

## Big inline editing

This one is much more powerful:

![Big inline editing](https://github.com/contributte/datagrid/blob/master/.docs/assets/big_inline_edit.gif?raw=true)

Example useage:

```php
/**
 * @var Contributte\Datagrid\Datagrid
 */
$grid = new Datagrid($this, $name);

/**
 * Big inline editing
 */
$grid->addInlineEdit()
	->onControlAdd[] = function(Nette\Forms\Container $container): void {
		$container->addText('id', '');
		$container->addText('name', '');
		$container->addText('inserted', '');
		$container->addText('link', '');
	};

$grid->getInlineEdit()->onSetDefaults[] = function(Nette\Forms\Container $container, $item): void {
	$container->setDefaults([
		'id' => $item->id,
		'name' => $item->name,
		'inserted' => $item->inserted->format('j. n. Y'),
		'link' => $item->name,
	]);
};

$grid->getInlineEdit()->onSubmit[] = function($id, Nette\Utils\ArrayHash $values): void {
	/**
	 * Save new values
	 */
};
```

### What happens after editing

By default, after submitting inline edit, the row is redrawn and the green animated background is triggered. Bud if you want to do something else, you can, just create new listener to event `InlineEdit::onCustomRedraw()`:

```php
/**
 * This callback will redraw the whole grid
 */
$grid->getInlineEdit()->onCustomRedraw[] = function() use ($grid): void {
	$grid->redrawControl();
};
```

### Show non editing Columns

If you don't want to set all columns editable, you may want to show these columns normally rendered:

```php
$grid->getInlineEdit()->setShowNonEditingColumns();
```
