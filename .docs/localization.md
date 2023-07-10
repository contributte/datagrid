Table of contents

- [Localization](#localization)
	- [Filters localization](#filters-localization)

# Localization

As you can see in the example below, a `SimpleTranslator` class comes with datagrid (the example was translated to czech). You can use it as shown (you will do that probably in some factory for all datagrids in your application). Of course, you can use your own translator - it just has to implement `Nette\Localization\Translator`.

```php
public function createComponentLocalizationGrid($name): Contributte\Datagrid\Datagrid
{
	$grid = new Datagrid($this, $name);

	$grid->setDataSource($this->ndb->table('example'));

	$grid->addColumnNumber('id', 'Id')
		->setAlign('start')
		->setSortable();

	$grid->addColumnText('name', 'Name')
		->setSortable();

	$grid->addColumnDateTime('inserted', 'Inserted');

	$translator = new Contributte\Datagrid\Localization\SimpleTranslator([
		'contributte_datagrid.no_item_found_reset' => 'Žádné položky nenalezeny. Filtr můžete vynulovat',
		'contributte_datagrid.no_item_found' => 'Žádné položky nenalezeny.',
		'contributte_datagrid.here' => 'zde',
		'contributte_datagrid.items' => 'Položky',
		'contributte_datagrid.all' => 'všechny',
		'contributte_datagrid.from' => 'z',
		'contributte_datagrid.reset_filter' => 'Resetovat filtr',
		'contributte_datagrid.group_actions' => 'Hromadné akce',
		'contributte_datagrid.show_all_columns' => 'Zobrazit všechny sloupce',
		'contributte_datagrid.hide_column' => 'Skrýt sloupec',
		'contributte_datagrid.action' => 'Akce',
		'contributte_datagrid.previous' => 'Předchozí',
		'contributte_datagrid.next' => 'Další',
		'contributte_datagrid.choose' => 'Vyberte',
		'contributte_datagrid.execute' => 'Provést',

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
