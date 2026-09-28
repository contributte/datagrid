![](https://heatbadger.now.sh/github/readme/contributte/datagrid/)

<p align=center>
	<a href="https://github.com/contributte/datagrid/actions"><img src="https://badgen.net/github/checks/contributte/datagrid/master?cache=300"></a>
	<a href="https://codecov.io/gh/contributte/datagrid"><img src="https://badgen.net/codecov/c/github/contributte/datagrid"></a>
	<a href="https://packagist.org/packages/ublaboo/datagrid"><img src="https://badgen.net/packagist/dm/ublaboo/datagrid"></a>
	<a href="https://packagist.org/packages/ublaboo/datagrid"><img src="https://badgen.net/packagist/v/ublaboo/datagrid"></a>
</p>
<p align=center>
	<a href="https://packagist.org/packages/ublaboo/datagrid"><img src="https://badgen.net/packagist/php/ublaboo/datagrid"></a>
	<a href="https://github.com/contributte/datagrid"><img src="https://badgen.net/github/license/contributte/datagrid"></a>
	<a href="https://bit.ly/ctteg"><img src="https://badgen.net/badge/support/gitter/cyan"></a>
	<a href="https://bit.ly/cttfo"><img src="https://badgen.net/badge/support/forum/yellow"></a>
	<a href="https://contributte.org/partners.html"><img src="https://badgen.net/badge/sponsor/donations/F96854"></a>
</p>

<p align=center>
Website 🚀 <a href="https://contributte.org">contributte.org</a> | Contact 👨🏻‍💻 <a href="https://paveljanda.com">paveljanda.com</a>, <a href="https://f3l1x.io">f3l1x.io</a> | Twitter 🐦 <a href="https://twitter.com/contributte">@contributte</a>
</p>

<p align=center>
	<img src="https://github.com/contributte/datagrid/blob/master/.docs/assets/datagrid.gif">
</p>

Datagrid is a data grid component for Nette Framework. Give it a data source and it renders a table that your users
can filter, sort and paginate, as a flat table or as a tree, with every label ready for translation. Doctrine, Dibi,
Nextras, Nette Database, Elasticsearch and plain arrays work as data sources.

## Usage

To install the latest version of `ublaboo/datagrid`, use [Composer](https://getcomposer.org):

```bash
composer require ublaboo/datagrid
```

Requires PHP 8.2 or later and Nette 3.2.

Create the grid in a presenter, give it a data source and add columns:

```php
use Contributte\Datagrid\Datagrid;
use Nette\Application\UI\Presenter;
use Nette\Database\Explorer;

final class UserPresenter extends Presenter
{

	public function __construct(
		private Explorer $database,
	)
	{
		parent::__construct();
	}

	protected function createComponentGrid(): Datagrid
	{
		$grid = new Datagrid();
		$grid->setDataSource($this->database->table('user'));
		$grid->addColumnText('name', 'Name')
			->setSortable()
			->setFilterText();

		return $grid;
	}

}
```

Render it in the template with `{control grid}`. The grid expects Bootstrap 5, Font Awesome and its own JavaScript and
CSS on the page; the [assets](.docs/assets.md) page shows how to load them from a CDN or a bundler.

> [!TIP]
> See the grid in action in the [datagrid-skeleton demo](https://examples.contributte.org/datagrid-skeleton/) and
> its [source code](https://github.com/contributte/datagrid-skeleton).

## Documentation

For details on how to use this package, check out the [documentation](.docs).

## Versions

| State  | Version  | Branch   | Nette  | PHP     |
|--------|----------|----------|--------|---------|
| dev    | `^7.2`   | `master` | `3.2+` | `>=8.2` |
| stable | `^7.1`   | `master` | `3.2+` | `>=8.2` |
| stable | `^7.0`   | `master` | `3.2+` | `>=8.1` |
| stable | `^6.10`  | `master` | `3.0+` | `>=7.2` |

## Development

See [how to contribute](https://contributte.org/contributing.html) to this package.

This package is currently maintained by these authors.

<a href="https://github.com/paveljanda">
	<img width="80" height="80" src="https://avatars2.githubusercontent.com/u/1488874?v=3&s=80">
</a>

<a href="https://github.com/f3l1x">
	<img width="80" height="80" src="https://avatars0.githubusercontent.com/u/538058?v=3&s=80">
</a>

-----

Consider [supporting](https://contributte.org/partners.html) the **contributte** development team.
Thank you for using this package.
