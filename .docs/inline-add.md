Table of contents

- [Inline adding](#inline-adding)
	- [Position of new item row](#position-of-new-item-row)

# Inline adding

Since version `3.3.0` there is a feature "inline adding" available. Up above is a demo where you can try that out. Just hit the "plus" button, fill some inputs and save the container. Example implementation:

```php
$grid->addInlineAdd()
	->onControlAdd[] = function(Nette\Forms\Container $container) {
		$container->addText('id', '')->setAttribute('readonly');
		$container->addText('name', '');
		$container->addText('inserted', '');
		$container->addText('link', '');
	};

$grid->getInlineAdd()->onSubmit[] = function(Nette\Utils\ArrayHash $values): void {
	$v = '';

	foreach($values as $key => $value) {
		$v . ="$key: $value, ";
	}

	$v = trim($v,', ');

	$this->flashMessage("Record with values [$v] was added! (not really)", 'success');
	$this->redrawControl('flashes');
};
```

## Position of new item row

As you can see, new item row is rendered at the bottom of the table. You may change that and make datagrid render the new item row on the top:

```php
$grid->addInlineAdd()
	->setPositionTop(); // Or take it down again: ::setPositionTop(false)
```

## Limitation when using array datasource

When you use array datasource, there is one limitation. Simply redrawing the grid won't do. You will also have to set the datasource again to refresh the data.

```php
$grid->getInlineAdd()->onSubmit[] = function(ArrayHash $values) use ($grid): void {
    $grid->setDatasource($this->model->getDatasource());
    $this->redrawControl();
};
```
