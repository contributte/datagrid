DataGrid
========

### Vytvoření komponenty:

```php
public function createComponentGrid($name)
{
	$grid = new Ublaboo\DataGrid\DataGrid($this, $name);
}
```

### `::setDataSource`: nastavení zdroje dat

DataGrid umí vypisovat (&& stránkovat) zatím `array`, `DibiFluent` (i při použití mssql databáze) a cokoliv, co implementuje `DataSource\IDataSource`.

```php
$grid->setDataSource($dibi->select('*')->from('user'));
```

### `::setItemsPerPageList`: nastavení počtů položek na stránku

```php
$grid->setItemsPerPageList([10, 50, 100, 200]);
```

### `::setTemplateFile`: šablona gridu

Pokud nastavíme odlišnou šablonu gridu, můžeme přepsat buď celou šablonu, nebo pouze některé bloky. Například `{block data}` obaluje výpis dat, ale filtry, řazení, stránkování apod zůstane stejné. Sekce jako filtry, stránkování apod tak zůstanou zachovány. Musíme však v takovém případě dědit od rodičovské šablony:

```php
{extends $original_template}
```

```php
$grid->setTemplateFile(APP_DIR . '/grid.latte');
```

### `::setPagination`: vypnutí stránkování

```php
$grid->setPagination(FALSE);
```

### `::setPrimaryKey`: nastavení primárního klíče (`id` je výchozí)

```php
$grid->setPrimaryKey('email');
```

### `::addColumnText`: přidání textového sloupce sloupce (`ColumnText`)

```php
$grid->addColumnText('username', 'Login');
```

Můžeme přidat více sloupců stejného sloupce tabulky z databáze. Sloupec databáze určíme třetím parametrem.

```php
$grid->addColumnText('username_again', 'Login znovu', 'username');
```

### `::addColumnNumber`: sloupec může být nastaven jako "řadící"
```php
$grid->addColumnNumber('id', 'ID')
	->setSortable();
```
### `::ColumnNumber`: má možnost formátování čísla. Textový sloupec nemá žádné výchozí formátování.

```php
$grid->addColumnNumber('id', 'ID');
```

Filtru můžeme změnit formátování.

```php
$grid->addColumnNumber('id', 'ID')
	->setFormat($decimals, $dec_point, $thousands_sep);
```

### `::addColumnDateTime`: column date/time/datetime/...

```php
$grid->addColumnDateTime('inserted', 'Vloženo (datum)');
```

Opět můžeme změnit formát

```php
$grid->addColumnDateTime('inserted', 'Vloženo (datum)')
	->setFormat('j. n. Y'); // Výchozí formát
```

Zobrazení času:

```php
$grid->addColumnDateTime('inserted', 'Vloženo (čas)', 'inserted')
	->setFormat('H:i:s');
```

### `::addColumnLink`: prolink do editace položky

```php
$grid->addColumnLink('edit', 'Položka', 'name');
```

Cíl odkazu určíme čtvrtým parametrem. Pokud chceme změnit i výčet odesílaných parametrů (ve výchozím stavu de odesílá `$primary`).

```php
$grid->addColumnLink('edit', 'Login', 'name', 'Customers:edit', ['email', 'name']);
```

Pokud chceme mapovat odesílané parametry na jiná jména parametrů v url, není to problém. Pošleme hodnotu sloupce id jako parametr `boo`:

```php
$grid->addColumnLink('edit', 'Login', 'name', 'Customers:edit', ['boo' => 'id']);
```

Tyto zápisy jsou totožné

```
$grid->addColumnLink('edit', 'Login', 'name');
$grid->addColumnLink('name', 'Login', 'name', 'edit');
$grid->addColumnLink('name', 'Login', 'name', 'edit', 'id');
```

### `::setReplacement`: sloupci můžeme nastavit "replacement"

```php
$grid->addColumnNumber('replacement', 'Replace deset/dvacet', 'id')
	->setReplacement([10 => 'deset', 20 => 'dvacet']);
```

### `::setAlign`: data sloupce mohou být zarovnána doprava/doleva/na střed

```php
$grid->addColumnNumber('replacement', 'Replace deset/dvacet', 'id')
	->setAlign('right');
```

### `::setRenderer`: sloupci můžeme nastavit vlastní renderer

```php
$grid->addColumnText('elephant', 'Zvíře')
	->setRenderer(function($item) {
		return str_repeat('Slon', $item->id);
	});
```

### `::setTemplate`: vlastní template položky sloupce

```php
$grid->addColumnText('status', 'Status')
	->setTemplate(__DIR__ . '/../templates/Examples/status.latte');
```

### `::addAction`: přidání akce gridu

```php
$grid->addAction('hello');
```

Titulek tlačítka

```php
$grid->addAction('hello', 'Objednat');
```

Ikona tlačítka

```php
$grid->addAction('hello')->setIcon('sun-o');
```

Atribut `title` tlačítka

```php
$grid->addAction('hello')->setTitle('Hello, sun');
```

Třetí parametr určuje destinaci linku, čtvrtý seznam přeposlaných parametrů.

Nad sloupci a akcemi můžeme volat metody bez `set` předpony. Všechny akce mají "fluent" interface.

Dále můžeme určit třídu tlačítka akce a potvrzovací hlášku

```php
$grid->addAction('delete', '', 'delete!')
	->icon('trash')
	->title('Smazat')
	->class('btn btn-xs btn-danger ajax')
	->confirm('Opravdu chcete smazat příklad %s', 'name');
```

Pokud je akce volána ajaxově (viz příklad výše), můžeme v handleru akce pouze překreslit nezbytený segment stránky, nikoliv přesměrovávat na novou stránku:

```php
public function handleDelete($id)
{
	# code here

	$this['productsGrid']->redrawControl('data');
}
```
I akce může mít svoji šablonu

```php
$grid->addAction('akce20', '')
	->setTemplate(__DIR__ . '/../templates/Examples/icon.latte');
```

### `::addFilterText`: přidání filtru

```php
$grid->addFilterText('name', 'Hledat:', ['name']);
```

`FilterText` může vyhledávat ve více sloupcích:

```php
$grid->addFilterText('all', 'Hledat:', ['name', 'id']);
```

`FilterText` může mít placeholder:

```php
$grid->addFilterText('all', 'Hledat:', ['name', 'id'])
	->setPlaceholder('Vyhledávání');
```

**Všechny filtry mohou mít vlastní podmínku vyhledávání**:

```php
$grid->addFilterText('custom', 'Custom vyhledávač:', ['name'])
	->setCondition(function($fluent, $value) {
		$fluent->where('id > ?', strlen($value)); // DataSource je zde DibiFluent
	});
```

### `::addFilterSelect`: filtr select...

```php
$grid->addFilterSelect('name', 'Hledat:', ['' => 'Vše', 'franta' => 'franta', 'pepa' => 'pepa']);
```

### `::addFilterDate`: filtr s datepickerem

```php
$grid->addFilterDate('inserted', 'Vloženo:');
```

### `::addFilterDateRange`: filtr s datepickerem - dvě políčka pro výběr "od - do"

```php
$grid->addFilterDateRange('from_to', 'Vloženo:', 'inserted');
```

### `::addExportCallback`: exportování dan, operace s filtrovanými/nefiltrovanými daty

DataGrid nabízí vlastní export-callbacky, tedy spuštění vlastních callbacků nad danými daty. Způsob volání: `DataGrid::addExportCallback($text, $callback, $filtered = FALSE)`

`$text` je popis tlačítka (možná pouze ikonka ->setIcon(), nebo jen ->icon()...)
`$callback` je vlastní callback
`$filtered` je parametr, který určuje, zda se mají předat callbacku data vyfiltrovaná, či všechna

Export lze spustit ajaxově:

```php
$grid->addExportCallback('Dump do ajax rq', function($data_source, $grid) {
	dump(sizeof($data_source)); die;
})->setAjax();
```

### `::addExportCsv`: export dat do CSV

V DataGridu jsou od základu připraveny dva hotové callbacky pro export dat do CSV
V exportu se již uplatní nastavené `replacements`, formátováni apod.

Všem exportům je možné nastavit atribut title.

```php
$grid->addExportCsv('Csv export', 'examples.csv')->title('Csv export');
```

### `::addExportCsvFiltered`: export filtrovaných dat do CSV

```php
$grid->addExportCsvFiltered('Csv export (filtr)', 'examples.csv')->title('Csv export (filtr)');
```

Pokud bychom chtěli jiné sloupce či jiné formátování stejných sloupců v exportu, můžeme vytvořit nové sloupce, které použijeme pouze pro exporty. Tyto sloupce mohou mýt opět i vlastní renderery, replacementy, apod.

```php
$column_name = new Ublaboo\DataGrid\Column\ColumnText('name', 'Jméno');
$column_even = (new Ublaboo\DataGrid\Column\ColumnText('even', 'Sudé ID (ano/ne)'))
	->setRenderer(function($item) { return $item->id % 2 ? 'Ne' : 'Ano'; });

$grid->addExportCsv('Csv export', 'examples_all.csv')
	->title('Csv export')
	->setColumns([
		$column_name,
		$column_even
	]);
```

### `::addGroupAction`: hromadné akce

DataGrid podporuje tvorbu hromadných akci (se sub-akcemi i bez):

```php
$grid->addGroupAction('Smazat')->onSelect[] = [$this, 'deleteMany'];

$grid->addGroupAction('Změna stavu objednávky', [
	1 => 'Přijatá',
	6 => 'Připraveno (poslat výzvu k platbě)',
	2 => 'Zpracovává se',
	3 => 'Odesláno zákazníkovi',
	4 => 'Připraveno na prodejně',
	8 => 'Vyzvednuto zákazníkem',
	5 => 'Storno'
])->onSelect[] = [$this, 'groupChangeStatus'];

$grid->addGroupAction('Odeslat', [
	'joseph' => 'Pepku Námořníkovi',
	'vlada'  => 'Putinovi',
	'ewa'    => 'Ewě Farne',
	'mario'  => 'Mariovi'
])->onSelect[] = [$this, 'groupSend'];
```

Tyto akce jsou volány ajaxově. Pokud tedy v handleru zavoláme: `$this['productsGrid']->redrawControl('data');`, překreslí se pouze nutný segment stránky, nikoliv stránka celá.

Handler jednoduché akce může vypadat takto:

```php
public function deleteMany(array $ids)
{
	# code here

	$this['productsGrid']->redrawControl('data');
}
```

Handler akce se sub-akcemi vypadá například takto:

```php
public function groupChangeStatus(array $ids, $status)
{
	# code here

	$this['productsGrid']->redrawControl('data');
}
```

### `::setTranslator()`: Slovník

DataGridu můžeme nastavit překladač, který implementuje `Nette\Localization\ITranslator`. Docílíme toho jednoduše:

```php
$grid->setTranslator($translator);
```

Ve výchozím stavu má DataGrid nastavený `Ublaboo\DataGrid\Localization\SimpleTranslator`, který používá asociativní pole (český text => překlad). V případě, že nenalezne překlad, vrátí předaný řetězec. Můžeme použít tento jednoduchý slovník:

```php
$grid->getTranslator()->setDictionary(['Položky' => 'Items', ...]);
```

### `::setTreeView()`: Stromový výpis

DataGrid disponuje možností výpisu položek jako stromu. Docílíme toho jednoduše.

1. Jako data source nastavíme položky první úrovně
2. Metodě `::setTreeView` předáme jako první parametr callback, který bude dostávat parametrem ID (jinou primary value) rodiče a vracet jeho potomky a jako druhý parametr název sloupce, ve kterém je "truthy" hodnota určijící, zda má položka potomky. Jako defaultní se uvažuje sloupec `has_children`. V příkladu níže je uveden.

Grido factory:

```php
$grid->setDataSource($this->categoryRepository->getAdminSelection());
$grid->setTreeView([$this->categoryRepository, 'getAdminSelection']);
```

CategoryRepository:

```php
public function getAdminSelection($parent = NULL)
{
	$join = $this->connection->select('id, name, parent')
		->from($this->getTable())
		->groupBy('parent');

	$selection = $this->connection
		->select('c.*, c_b.name as has_children')
		->from($this->getTable(), 'c')
		->leftJoin($join, 'c_b')
			->on('c_b.parent = c.id')
		->orderBy('c.seq ASC');

	if (NULL === $parent) {
		$selection->where('c.parent IS NULL');
	} else {
		$selection->where('c.parent = ?', (int) $parent);
	}

	return $selection;
}
```

Důležitá část je ta s ifkou. Jakmile chce DataGrid získat potomky, poskytne mi zmíněnou primary value rodiče.

### Změna statusu položky

Pokud budeme chtít provádět nějaké ajaxové operace nad jednou položkou, nemá smysl překreslovat celý strom. Následuje příklad s handlerem, který změní status jedné kategorii a nechá DataGrid ajaxově překreslit pouze jeden řádek:

```php
private function handleChangeStatus($id, $status)
{
	$this->categoryRepository->changeStatus($id, $status);
	$this['categoriesGrid']->setDataSource($this->categoryRepository->getAdminSelection('all'));

	$this->flashMessage('Stav kategorie byl změněn', 'success notification');

	if ($this->isAjax()) {
			$this['categoriesGrid']->redrawItem($id, 'c.id');
	} else {
		$this->redirect('this');
	}
}
```

Jak je vidět, voláme getAdminSelection s parametrem 'all'. Pojďmě ukázat upravenou metodu v **CategoryRepository**:

```php
public function getAdminSelection($show = NULL)
{
	/**
	 * Prepare select for finding end points
	 */
	$join = $this->connection->select('id, name, parent')
		->from($this->getTable())
		->groupBy('parent');

	$selection = $this->connection
		->select('c.*, c_b.name as has_children')
		->from($this->getTable(), 'c')
		->leftJoin($join, 'c_b')
			->on('c_b.parent = c.id')
		->orderBy('c.seq ASC');

	if (is_numeric($show)) {
		$selection->where('c.parent = ?', (int) $show);
	} else if (NULL === $show) {
		$selection->where('c.parent IS NULL');
	} else if ('all' === $show) {
		# Do not filter selection
	}

	return $selection;
}
```

DataGrid v zavolané metodě `::redrawItem` potřebuje stejnou selekci, na které později specifikuje výběr pouze jedné položky. Musí však už v základu obsahovat zvolenou položku, aby ji DataGrid později našel.

## Příklad použití

```php
public function createComponentExamplesGrid($name)
{
	$grid = new Ublaboo\DataGrid\DataGrid($this, $name);

	$grid->setItemsPerPageList([10, 50, 100, 200]);

	$grid->setDataSource($this->exampleRepository->getAdminSelection());

	$grid->addColumnLink('edit', 'Login', 'name');

	$grid->addColumnNumber('id', 'ID')
		->setSortable();

	$grid->addColumnNumber('replacement', 'Replace deset/dvacet', 'id')
		->format(1)
		->setReplacement([10 => 'deset', 20 => 'dvacet']);

	$grid->addColumnText('custom_renderer', 'Sude', 'id')
		->setRenderer(function($item) {
			return $item->id % 2 == 0 ? 'Yes' : 'No';
		});

	$grid->addAction('hello')->icon('sun-o')->title('Hello, sun');

	$grid->addAction('delete', '', 'delete!')
		->icon('trash')
		->title('Smazat')
		->class('ajax btn btn-xs btn-danger')
		->confirm('Opravdu chcete smazat příklad %s', 'name');

	$grid->addFilterSelect('name', 'Hledat:', ['' => 'Vše', 'yk95ak88ra' => 'yk95ak88ra', 'el25id2emd' => 'el25id2emd']);

	$grid->addColumnDateTime('inserted', 'Vloženo (datum)');
	$grid->addColumnDateTime('inserted2', 'Vloženo (time)', 'inserted')
		->setFormat('H:i:s');

	$grid->addColumnText('status', 'Delitelne 3')
		->setTemplate(__DIR__ . '/../templates/Examples/status.latte');

	$grid->addFilterText('custom', 'Custom vyhledávač:', ['name'])
		->setCondition(function($data_source, $value) {
			$data_source->where('id > ?', strlen($value)); // DataSource je zde DibiFluent
		});
	
	$grid->addFilterDate('inserted', 'Vloženo:');
	$grid->addFilterDateRange('from_to', 'Vloženo:', 'inserted');

	$grid->addExportCallback('Dump do ajax rq', function($data_source, $grid) {
		dump(sizeof($data_source)); die;
	})->setAjax();

	$grid->addExportCsvFiltered('Csv export (filtr)', 'examples.csv')->title('Csv export (filtr)');

	$column_name = new Ublaboo\DataGrid\Column\ColumnText('name', 'Jméno');
	$column_even = (new Ublaboo\DataGrid\Column\ColumnText('even', 'Sudé ID (ano/ne)'))
		->setRenderer(function($item) { return $item->id % 2 ? 'Ne' : 'Ano'; });

	$grid->addExportCsv('Csv export', 'examples_all.csv')
		->title('Csv export')
		->setColumns([
			$column_name,
			$column_even
		]);

	$grid->addGroupAction('Změna stavu objednávky', [
		1 => 'Přijatá',
		6 => 'Připraveno (poslat výzvu k platbě)',
		2 => 'Zpracovává se',
		3 => 'Odesláno zákazníkovi',
		4 => 'Připraveno na prodejně',
		8 => 'Vyzvednuto zákazníkem',
		5 => 'Storno'
	])->onSelect[] = [$this, 'groupChangeStatus'];

	$grid->addGroupAction('Odeslat', [
		'joseph' => 'Pepku Námořníkovi',
		'vlada'  => 'Putinovi',
		'ewa'    => 'Ewě Farne',
		'mario'  => 'Mariovi'
	])->onSelect[] = [$this, 'groupSend'];

	$grid->addGroupAction('Smazat')->onSelect[] = [$this, 'deleteMany'];
}


public function groupChangeStatus(array $ids, $status)
{
	dump($ids, "Menim status na: [$status]"); die;
}


public function groupSend(array $ids, $key)
{
	dump($ids, "Poslat status prijemci: [$key]"); die;
}


public function deleteMany(array $ids)
{
	dump('Mazu tato ID: ', $ids); die;
}


public function handleDelete($id)
{
	$this->flashMessage('Příklad smazán.', 'info notification');

	if ($this->isAjax()) {
		$this['examplesGrid']->redrawControl('data');
	} else {
		$this->redirect('this');
	}
}
```

Za zmínku jistě stojí, že DataGrid zvládne překreslit i samotný řádek. To se hodí v situacích, kdy například změnit stav položky:

```php
private function handleChangeStatus($id, $status)
{
	# code ...

	$this->flashMessage('Stav položky byl změněn', 'success notification');

	if ($this->isAjax()) {
		$this['productsGrid']->redrawItem($id);
	} else {
		$this->redirect('this');
	}
}
```

## Pořadí parametrů (`addFilter*`, `addColumn`, `addAction*`)

Jsou dva povinné parametry (**`addColumn*`**):

**První** parametr metod addColumnText, addColumnLink, addColumnNumber, addColumnDateTime je unikátní klíč, pod kterým se ukládají sloupce v příslušném poli DataGridu (::$columns).

**Druhý** parametr je název sloupce.

Pokud nejsou uvedeny další parametry, tak se za sloupec považuje první parametr - klíč. U sloupce "odkaz" taktéž. Tedy pokud je uvedeno:
```php
$grid->addColumnLink('name', 'Položka')
```
, tak se bude používat sloupec db tabulky `name`, sloupec gridu se bude jmenovat "Položka" a odkaz bude vypadat nějak takto: `href="name, id => 1"`. Většinou se ale vypisuje jiný sloupec, než jaký má název odkaz:

```php
$grid->addColumnLink('edit', 'Položka', 'title')
```

Nebo může být být odkaz typu signál. To by se specifikovalo takto:

```php
$grid->addColumnLink('edit', 'Položka', 'title', 'edit!')
```

K tomu se ještě mohou posílat jiné parametry, než primární klíč:

```php
$grid->addColumnLink('edit', 'Položka', 'title', 'deleteAll!', 'gender')
```

No a konečně se může posílat parametr(y) jiného jména, než je/jsou sloupec/sloupce tabulky v databázi:

```php
$grid->addColumnLink('edit', 'Položka', 'title', 'edit!', ['id' => 'gender'])
```

Podobně fungují filtry (též je první parametr klíč, který se defaultně považuje za sloupec) nebo akce.
