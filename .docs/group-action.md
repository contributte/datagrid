Table of contents

- [Group action](#group-action)
	- [Api](#api)
		- [One level](#one-level)
		- [Two level](#two-level)
		- [Text input](#text-input)
		- [Textarea](#textarea)
		- [Attributes, classes](#attributes-classes)
	- [Happy inputs](#happy-inputs)

# Group action

## Api

### One level

If you need to do some operations with multiple rows, there are group actions. There are one or two level group actions. One level:

```php
$grid->addGroupAction('Delete examples')->onSelect[] = [$this, 'deleteExamples'];
$grid->addGroupAction('Something alse')->onSelect[] = [$this, 'doSomethingElse'];
```

This will create one select box (['Delete examples', 'Something alse']) and submit button. If you submit that form, your handler will be called. It will be called via ajax.

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

### Two level

There is also the two-level possibility of group action:

```php
$grid->addGroupAction('Change order status', [
	1 => 'Received',
	2 => 'Ready',
	3 => 'Processing',
	4 => 'Sent',
	5 => 'Storno'
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
		sprintf("Status of items with id: [%s] was changed to: [$status]", implode($ids, ',')),
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

### Text input

Group action can also containe a text input instad of select (As show in example above - option called "**Add note**"). Example code:

```php
$grid->addGroupTextAction('Add note')
	->onSelect[] = [$this, 'addNote'];
```

And the `::addNote()` method:

```php
public function addNote(array $ids, $value): void
{
	$this->flashMessage(
		sprintf('Note [%s] was added to items with ID: [%s]', $value, implode($ids, ',')),
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

### Textarea

User may also use a textarea:

```php
$grid->addGroupTextareaAction('aaaa');
```

### Attributes, classes

All group action inputs have optional class or other attributes:

```php
$grid->addGroupTextareaAction('aaaa')
	->setAttribute('rows', 10)
	->setClass('fooo');
```

## Happy inputs

DataGrid uses tiny library `happy` for those nice checkboxes. You can disable them:

```php
$grid->useHappyComponents(false);
```
