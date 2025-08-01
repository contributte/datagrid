# Group action

- [Api](#api)
    - [Zero level](#zero-level)
    - [One level](#one-level)
    - [Two level](#two-level)
    - [Text input](#text-input)
    - [Textarea](#textarea)
    - [Attributes, classes](#attributes-classes)

-----

# Api

## Zero level

![Group button action](https://github.com/contributte/datagrid/blob/master/.docs/assets/group_button_action.gif?raw=true)

When you want to show just one action button, do simply that:

```php
$grid->addGroupButtonAction('Say hello')->onClick[] = [$this, 'sayHello'];
```

## One level

![Group action 1](https://github.com/contributte/datagrid/blob/master/.docs/assets/group_button_action_1.gif?raw=true)

```php
$grid->addGroupAction('Delete examples')->onSelect[] = [$this, 'deleteExamples'];
$grid->addGroupAction('Something else')->onSelect[] = [$this, 'doSomethingElse'];
```

This will create one select box (['Delete examples', 'Something else']) and submit button. If you submit that form, your handler will be called. It will be called via ajax.

This is how your handler can look like:

```php
public function deleteExamples(array $ids): void
{
	// Your code...

	if ($this->isAjax()) {
		$this['groupActionsGrid']->reload();
	} else {
		$this->redirect('this');
	}
}
```

## Two level

![Group action 2](https://github.com/contributte/datagrid/blob/master/.docs/assets/group_button_action_2.gif?raw=true)

There is also the two-level possibility of group action:

```php
$grid->addGroupAction('Change order status', [
	1 => 'Received',
	2 => 'Ready',
	3 => 'Processing',
	4 => 'Sent',
	5 => 'Canceled'
])->onSelect[] = [$this, 'groupChangeStatus'];

$grid->addGroupAction('Send', [
	'john'  => 'John',
	'joe'   => 'Joe',
	'frank' => 'Franta',
])->onSelect[] = [$this, 'groupSend'];
```

And some example handler:

```php
public function groupChangeStatus(array $ids, $status): void
{
	$this->flashMessage(
		sprintf("Status of items with id: [%s] was changed to: [$status]", implode(',', $ids)),
		'success'
	);

	$this->exampleRepository->updateStatus($ids, $status);

	if ($this->isAjax()) {
		$this->redrawControl('flashes');
		$this['groupActionsGrid']->reload();
	} else {
		$this->redirect('this');
	}
}
```

## Text input

Group action can also contain a text input instead of select (As show in example above - option called "**Add note**"). Example code:

```php
$grid->addGroupTextAction('Add note')
	->onSelect[] = [$this, 'addNote'];
```

And the `::addNote()` method:

```php
public function addNote(array $ids, $value): void
{
	$this->flashMessage(
		sprintf('Note [%s] was added to items with ID: [%s]', $value, implode(',', $ids),
		'success'
	);

	if ($this->isAjax()) {
		$this->redrawControl('flashes');
		$this['groupActionsGrid']->reload();
	} else {
		$this->redirect('this');
	}
}
```

## Textarea

User may also use a textarea:

```php
$grid->addGroupTextareaAction('aaaa');
```

## Attributes, classes

All group action inputs have optional class or other attributes:

```php
$grid->addGroupTextareaAction('aaaa')
	->setAttribute('rows', 10)
	->setClass('fooo');
```
