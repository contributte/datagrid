# Contributte Datagrid

DataGrid component for Nette Framework with filtering, sorting, pagination and tree view.

## Stack

- Language: PHP >=8.2
- Framework: Nette 3.2 (Application, Forms, DI), Nette Utils 4, Symfony PropertyAccess
- Tests: Nette Tester; static analysis: PHPStan (level 8); code style: Contributte coding standard
- Assets: TypeScript and CSS bundled with Rollup (npm)

## Development

```bash
make install     # install dependencies
make qa          # PHPStan and code style
make csf         # fix code style
make tests       # run all tests
vendor/bin/tester -s -p php --colors 1 -C tests/Cases/ItemsPerPageTest.phpt   # run one test file
make coverage    # code coverage
npm install && npm run build   # build JS/CSS assets
```

All targets are defined in the `Makefile`.

## Principles

- KISS: write the simplest code that works; no speculative abstractions.
- DRY: one source of truth; reuse existing code before adding new.
- YAGNI: build what is needed now, not what might be needed later.
- Small, final classes with typed properties and `declare(strict_types = 1)`.
- Every change comes with a test; `make qa tests` must pass before a commit.
