# Contributte Datagrid

Instructions for AI coding agents working in this repository.

## Overview

`contributte/datagrid` is a data grid component for Nette Framework. `Contributte\Datagrid\Datagrid` is a Nette
`Control` that takes a data source and renders a Bootstrap 5 table with filters, sorting, pagination, inline editing,
group actions, exports and a tree view. The repository ships PHP code, Latte templates and TypeScript and CSS assets.
It is a library without a DI extension, not an application.

- **PHP**: 8.2 or later (`>=8.2` in `composer.json`); CI runs the tests on PHP 8.3 to 8.5
- **Package**: `ublaboo/datagrid` on Packagist (the old vendor name), namespace `Contributte\Datagrid\`
- **Assets**: npm package `@contributte/datagrid`, TypeScript and CSS in `assets/`, bundled by rollup into `dist/`
- **Integrates**: `nette/application` and `nette/forms` 3.2, `symfony/property-access` 6.4 to 8.x, data sources
  for Doctrine, Dibi, Nextras, Nette Database, Elasticsearch and plain arrays

## Documentation

- `.docs/README.md` is the table of contents of the user documentation, split into `.docs/*.md` by topic. Update the
  matching page in the same pull request when behaviour changes.
- `DESIGN.md` describes the templates, CSS, icons and UI states. Read it before changing `src/templates/`,
  `src/Components/DatagridPaginator/templates/` or `assets/`.
- `UPGRADE.md` lists breaking changes per major and minor version. Add an entry for every renamed block, class,
  translation key or public method.
- `.claude/agents/php-changelog-generator.md` is a shared subagent that drafts `UPGRADE.md` and `CHANGELOG.md` entries.
- Organization rules for code, tests and tooling are in
  [contributte/contributte specs](https://github.com/contributte/contributte/tree/master/specs).

## Commands

```bash
# Install PHP dependencies
make install

# Run all checks (code style + PHPStan level 8)
make qa

# Fix code style
make csf

# Run all tests, or one file
make tests
vendor/bin/tester -s -p php --colors 1 -C tests/Cases/FilterTest.phpt

# Install npm dependencies and build dist/datagrid-full.js and dist/datagrid-full.css
npm install
npm run build

# Rebuild the bundle on every change
npm run dev
```

The `Makefile` has no `help` target; the targets are `install`, `qa`, `cs`, `csf`, `phpstan`, `tests` and `coverage`.

## Conventions

- Tests are `TestCase` classes in `.phpt` files in `tests/Cases`, namespace `Contributte\Datagrid\Tests\Cases`, and
  end with `->run()`. Presenters and grid factories they share live in `tests/Files`, not `tests/Fixtures`.
- `tests/bootstrap.php` uses `Tester\Environment` directly, not `contributte/tester`. Don't mix in `Toolkit::test()`.
- Exceptions live in `src/Exception/` as `Datagrid*Exception` classes that mostly extend `\Exception` directly.
  There is no `LogicalException` or `RuntimeException` base; changing a parent class is a breaking change.
- Every user-facing string in a template goes through `|translate` with a `contributte_datagrid.*` key, and the
  English default is added to `src/Localization/SimpleTranslator.php`.

## Traps

- **Template blocks are public API.** Applications extend `datagrid.latte` with `{extends $originalTemplate}` and
  override blocks such as `table-class`, `noItems`, `col-{key}` and `icon-*`. Renaming or removing one breaks them.
- **CSS classes and `data-*` attributes are read by the TypeScript plugins.** `data-datagrid-name`, `data-check`,
  `data-sortable`, `.datagrid-inline-edit` and similar names in `src/templates/` are selectors in `assets/plugins/`.
  Change both sides together.
- **`dist/` is not committed.** It is in `.gitignore`; the `assets.yml` workflow builds it on `master` and publishes
  `@contributte/datagrid` with the `master` tag only on a manual dispatch. The CDN serves those files.
- **The Composer name is still `ublaboo/datagrid`.** The GitHub repository and namespace are `contributte`, but badges,
  install commands and asset import paths (`vendor/ublaboo/datagrid/assets`) must use the old name.
- **Data source tests need MySQL.** `tests/Cases/DataSources/*` connect to `127.0.0.1`, user `root`, database `tests`
  (see `nextrasDatasource.ini`) and skip without `mysqli`. CI provides MySQL through `nette-tester-mysql.yml`.
- **The `test82` job in `tests.yml` runs PHP 8.3.** PHP 8.2 is allowed by `composer.json` but not tested in CI, and the
  `--prefer-lowest` job also runs on 8.3.
- **PHPStan runs at level 8 with an ignore list.** `phpstan.neon` includes the `phpstan/*` extensions directly and
  suppresses `missingType.generics` and `missingType.iterableValue`. Fix new errors instead of adding `ignoreErrors`.
- Usage, configuration and examples for users live in `.docs/README.md` and the pages it links, not here.
