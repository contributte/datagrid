# Localization

As you can see in the example below, a `SimpleTranslator` class comes with datagrid (the example was translated to czech). You can use it as shown (you will do that probably in some factory for all datagrids in your application). Of course you can use your own translator - it just has to implement `Nette\Localization\ITranslator`.

```php
public function createComponentLocalizationGrid($name): Ublaboo\DataGrid\DataGrid
{
	$grid = new DataGrid($this, $name);

	$grid->setDataSource($this->ndb->table('ublaboo_example'));

	$grid->addColumnNumber('id', 'Id')
		->setAlign('left')
		->setSortable();

	$grid->addColumnText('name', 'Name')
		->setSortable();

	$grid->addColumnDateTime('inserted', 'Inserted');

	$translator = new Ublaboo\DataGrid\Localization\SimpleTranslator([
		'ublaboo_datagrid.no_item_found_reset' => 'Žádné položky nenalezeny. Filtr můžete vynulovat',
		'ublaboo_datagrid.no_item_found' => 'Žádné položky nenalezeny.',
		'ublaboo_datagrid.here' => 'zde',
		'ublaboo_datagrid.items' => 'Položky',
		'ublaboo_datagrid.all' => 'všechny',
		'ublaboo_datagrid.from' => 'z',
		'ublaboo_datagrid.reset_filter' => 'Resetovat filtr',
		'ublaboo_datagrid.group_actions' => 'Hromadné akce',
		'ublaboo_datagrid.show_all_columns' => 'Zobrazit všechny sloupce',
		'ublaboo_datagrid.hide_column' => 'Skrýt sloupec',
		'ublaboo_datagrid.action' => 'Akce',
		'ublaboo_datagrid.previous' => 'Předchozí',
		'ublaboo_datagrid.next' => 'Další',
		'ublaboo_datagrid.choose' => 'Vyberte',
		'ublaboo_datagrid.execute' => 'Provést',

		'Name' => 'Jméno',
		'Inserted' => 'Vloženo'
	]);

	$grid->setTranslator($translator);
}
```

## Filters localization

All filters and their placeholders are translated normally except for `FilterSelect` and `FilterMultiSelect`.

You can change that behaviour:

```php
$grid->addFilterMultiSelect('status', 'Status:', [
	0 => 'Offline',
	1 => 'Online',
	2 => 'Standby'
])->setTranslateOptions(); // Or disable it again: ::setTranslateOptions(false)
```
