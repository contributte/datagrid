Table of contents

- [Actions](#actions)
    - [Api](#api)
        - [Parameters](#parameters)
        - [Icon](#icon)
        - [Class](#class)
        - [Title](#title)
        - [Confirmation](#confirmation)
    - [Ajax](#ajax)
        - [Redrawing the data](#redrawing-the-data)
        - [Redrawing one row](#redrawing-one-row)
    - [Sortable](#sortable)
        - [Sorting handler](#sorting-handler)
    - [MultiAction](#multiaction)
    - [Item detail](#item-detail)
        - [Item detail form](#item-detail-form)
        - [Item detail template variables](#item-detail-template-variables)
        - [Item detail render condition](#item-detail-render-condition)
    - [ActionCallback](#actioncallback)
    - [Toolbar button](#toolbar-button)

# Actions

## Api

### Parameters

Parameters are the same as in ColumnLink:

```php
/**
 * $key, $name = '', $href = $key, array $params = NULL
 */
$grid->addAction('edit', 'Edit');
```

### Icon

```php
$grid->addAction('edit', '')
	->setIcon('pencil');

$grid->addAction('send', '')
	->setIcon(function($item) { return $item->sent_already ? 'repeat' : 'envelope'; });
```

### Class

```php
$grid->addAction('dosth', '', 'doSomething!')
	->setIcon('pencil')
	->setClass('btn btn-xs btn-danger ajax');
```

Action class can be also defined by your callback. Row `$item` is passed to your callback as the only argument.

```php
$grid->addAction('send', '')
	->setIcon('envelope')
	->setClass(function($item) { return $item->sent_already ? 'btn btn-xs btn-success' : 'btn btn-xs btn-default' });
```

### Title

```php
$grid->addAction('edit', '')
	->setIcon('pencil')
	->setTitle('Edit row');
```

Action title can be defined by custom callback same as title or class.

### Confirmation

```php
$grid->addAction('delete', '', 'delete!')
	->setIcon('trash')
	->setTitle('Smazat')
	->setClass('btn btn-xs btn-danger <strong class="text-danger">ajax</strong>')
	->setConfirmation(
		new StringConfirmation('Do you really want to delete row %s?', 'name') // Second parameter is optional
	);
```

If you want to define confirmation dialog with a callback, the callback has to return string.

```php
$grid->addAction('delete', '', 'delete!')
	->setConfirmation(
		new CallbackConfirmation(
			function($item) {
				return 'Do you really want to delete row with id ' . $item->id . ' and name ' . $item->name . '?';
			}
		)
	);
```

## Ajax

### Redrawing the data

All links are by default not-ajax. Do you see the bold `ajax` class in previous example? The ajax handler could now look like this:

```php
public function handleDelete($id)
{
	$this->connection->delete('example')
		->where('id = ?', $id)
		->execute();

	$this->flashMessage("Item deleted [$id] (actually, it was not)", 'info');

	if ($this->isAjax()) {
		$this->redrawControl('flashes');
		$this['actionsGrid']->reload();
	} else {
		$this->redirect('this');
	}
}
```

### Redrawing one row

When you are updating row data (i.e. status), you can send only one row as snippet, not the whole table:

```php
public function handleSetStatus($id, $status)
{
	$this->connection->update('example', ['status' => $satatus])
		->where('id = ?', $id)
		->execute();

	$this->flashMessage("Status of item [$id] was updated to [$status].", 'success');

	if ($this->isAjax()) {
		$this->redrawControl('flashes');
		$this['actionsGrid']->redrawItem($id);
	} else {
		$this->redirect('this');
	}

```

## Sortable

You can tell datagrid to be sortable (drag &amp; drop):

```php
$grid->setSortable();
```

This will show a handle in your datagrid. When you reorder items in datagrid, a nette.ajax request is sent as a signal to handler `sort!` in your presenter. Handler of datagrid above could look like this:

```php
/**
 * @param  int      $item_id
 * @param  int|NULL $prev_id
 * @param  int|NULL $next_id
 * @return void
 */
public function handleSort($item_id, $prev_id, $next_id)
{
	$repository = $this->em->getRepository(Item::class);
	$item = $repository->find($item_id);

	/**
	 * 1, Find out order of item BEFORE current item
	 */
	if (!$prev_id) {
		$previousItem = NULL;
	} else {
		$previousItem = $repository->find($prev_id);
	}

	/**
	 * 2, Find out order of item AFTER current item
	 */
	if (!$next_id) {
		$nextItem = NULL;
	} else {
		$nextItem = $repository->find($next_id);
	}

	/**
	 * 3, Find all items that have to be moved one position up
	 */
	$itemsToMoveUp = $repository->createQueryBuilder('r')
		->where('r.order <= :order')
		->setParameter('order', $previousItem ? $previousItem->getOrder() : 0)
		->andWhere('r.order > :order2')
		->setParameter('order2', $item->getOrder())
		->getQuery()
		->getResult();

	foreach ($itemsToMoveUp as $t) {
		$t->setOrder($t->getOrder() - 1);
		$this->em->persist($t);
	}

	/**
	 * 3, Find all items that have to be moved one position down
	 */
	$itemsToMoveDown = $repository->createQueryBuilder('r')
		->where('r.order >= :order')
		->setParameter('order', $nextItem ? $nextItem->getOrder() : 0)
		->andWhere('r.order < :order2')
		->setParameter('order2', $item->getOrder())
		->getQuery()
		->getResult();

	foreach ($itemsToMoveDown as $t) {
		$t->setOrder($t->getOrder() + 1);
		$this->em->persist($t);
	}

	/**
	 * Update current item order
	 */
	if ($previousItem) {
		$item->setOrder($previousItem->getOrder() + 1);
	} else if ($nextItem) {
		$item->setOrder($nextItem->getOrder() - 1);
	} else {
		$item->setOrder(1);
	}

	$this->em->persist($item)->flush();

	$this->flashMessage("Id: $item_id, Previous id: $prev_id, Next id: $next_id", 'success');
	$this->redrawControl('flashes');

	$this['itemsGrid']->redrawControl();
}
```

### Sorting handler

The name of the handler used for sorting can be changed:

```php
$grid->setSortableHandler('foo!');
```

Also, when you are using datagrid in component, you have to alter the name a bit:

```php
$grid->setSortableHandler('myComponent:sort!');
```

## MultiAction

Same as there is column status with pretty dropdown menu, the datagrid comes with similar dropdown menu for actions. It is called MultiAction:

```php
/**
 * Multiaction
 */
$grid->addMultiAction('multi_action', 'MultiAction')
	->addAction('blah', 'Blahblah', 'blah!')
	->addAction('blah2', 'Blahblah2', 'blah!', ['name']);
```

Sure you can alter multiaction class, icons, etc. Same you can change icon, class, etc. of nested actions:

```php
$grid->getAction('multi_blah')
	->getAction('blah2')->setIcon('check');
```

## Item detail

As you can see in the demo page, there is a little eye button that toggles an item detail. This detail is loaded via ajax (just for the first time). The detail can be rendered in separate template or via custom renderer or in block `#detail`, which you define in you datagrid template. First, you need to enable the items detail functionality:

```php
/**
 * This will enable rendering item detail in #detail block in your datagrid template
 */
$grid->setItemsDetail($detail = TRUE, $primary_where_column = $grid->primary_key); // Or just $grid->setItemsDetail();

/**
 * That would include separate detail template
 */
$grid->setItemsDetail(__DIR__ . '/templates/Datagrid/grid_item_detail.latte');

/**
 * And here is used just simple renderer callback
 */
$grid->setItemsDetail(function() { return 'Lorem Ipsum'; });
```

### Item detail form

User may use a ItemDetail containing a form (`ItemDetailForm`). Example usage:

```php
$presenter = $this;

$grid->setItemsDetail();

$grid->setItemsDetailForm(function(Nette\Forms\Container $container) use ($grid, $presenter) {
	$container->addHidden('id');
	$container->addText('name');

	$container->addSubmit('save', 'Save')
		->setValidationScope([$container])
		->onClick[] = function($button) use ($grid, $presenter) {
			$values = $button->getParent()->getValues();

			$presenter['examplesGrid']->redrawItem($values->id);
		};
});
```

Datagrid user template:

```latte
{extends $originalTemplate}

{block detail}
	<h2>{$item->name}</h2>

	<p>Lorem ipsum ...</p>

	{formContainer items_detail_form}
		{formContainer items_detail_form_$item->id}
			{input id, value => $item->id}
			{input name, value => $item->name}
			{input save}
		{/formContainer}
	{/formContainer}

```

### Item detail template variables

Additional variables can be passed to item detail template (/block) via `ItemDetail::setTemplateParameters(['foo' => 'bar', ...])`

### Item detail render condition

Custom callback can be set to decide whether to render item detail or not:

```php
$grid->getItemsDetail()->setRenderCondition(function($item) {
	return TRUE;
});
```

## ActionCallback

You don't have to fire event only using `PresenterComponent` signals. There is a possibility to fire events directly:

```php
$grid->addActionCallback('custom_callback', '')
	->setIcon('sun-o')
	->setTitle('Hello, sun')
	->setClass('btn btn-xs btn-default ajax')
	->onClick[] = function($item_id) use ($presenter) {
		$presenter->flashMessage('Custom callback triggered, id: ' . $item_id);
		$presenter->redrawControl('flashes');
	};
```

You treat `ActionCallback` same as `Action`, except for some arguments passed to the `Datagrid::addActionCallback` method.

## Toolbar button

If you need simple links to custom destinations from datagrid toolbar, they can be added like this:

```php
$grid->addToolbarButton('this', 'Toolbar');
$grid->addToolbarButton('this', 'Button', ['foo' => 'bar']);
```

Additional attributes could be added to these buttons

```php
$grid->addToolbarButton('this', 'Toolbar')
	->addAttributes(['foo' => 'bar']);
```
