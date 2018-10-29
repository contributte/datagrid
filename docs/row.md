# Row

## Row conditions

### Allow Group Action

Now all rows have to provide group action or editing. Or some other of your actions. You can forbid group acitons rendering for some items like this:

```php
$grid->allowRowsGroupAction(function(Row $item): bool {
	return $item->id !== 2;
});
```

### Allow Inline Edit

Also inline editing cound be disabled for some rows:

```php
$grid->allowRowsInlineEdit(function(Row $item): bool {
	return $item->role === 'admin';
});
```

### Allow Actions

It works simalary when you want to allow actions for just some of your items:

```php
$grid->allowRowsAction('delete', function(Row $item): bool {
	return $item->id !== 3;
});
```

### Allow Action of MultiAction

In case you need to show user just some actions in MultiAction list:

```php
$grid->addMultiAction('goto', 'Go to')
	->addAction('profile', 'Profile', 'Profile:default')
	->addAction('settings', 'Settings', 'Settings:default')
	->addAction('homepage', 'Homepage', 'Homepage:default');

$grid->allowRowsMultiAction(
	'goto',
	'profile',
	function($item): bool {
		return $item->canDispleyProfile();
	}
);

$grid->allowRowsMultiAction(
	'goto',
	'settings',
	function($item): bool {
		return $item->canDispleySettings();
	}
);
```

## Row callback

If you want to alter table row class, you can do this with row callback:

```php
$grid->setRowCallback(function($item, $tr) {
	$tr->addClass('super-' . $item->id);
});
```

If you look at the example above, you will see that each row (`<tr>`) has class `super-<id>`.
