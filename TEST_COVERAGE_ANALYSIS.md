# Test Coverage Analysis

## Overview

The codebase has **98 PHP source files** and **25 test files** (`.phpt`), yielding a raw file ratio of roughly 1 test per 4 source files. However, file count alone understates the coverage gap: the largest and most complex source files are among the least tested.

| Metric | Value |
|--------|-------|
| Source files (`src/`) | 98 |
| Test files (`tests/Cases/`) | 25 |
| Largest source file | `Datagrid.php` — 2,966 lines |
| Test methods in `DatagridTest.phpt` | 3 |
| Completely untested source modules | 7 of 13 modules |

---

## Completely Untested Modules

The following source directories have **zero test coverage**:

### 1. `AggregationFunction/` — 6 files, ~250 lines

No tests exist for any aggregation functionality. This includes:

- `FunctionSum` — processes aggregations across Dibi, Doctrine, Collection, and Nextras data sources
- `TDatagridAggregationFunction` — complex trait managing single and multiple aggregation functions with mutual-exclusion guards and data-type-based branching

Key risk: the trait's validation logic (e.g. "cannot mix AggregationFunctions and MultipleAggregationFunction", "data source must implement IAggregatable") is entirely unverified.

### 2. `GroupAction/` — 7 files, ~450 lines

The entire group actions feature is untested. `GroupActionCollection` (291 lines) is particularly complex — it builds form containers with multiple passes, handles select/multi-select/text/textarea/button action subtypes, and processes submitted forms. None of this logic is covered.

### 3. `InlineEdit/` — 2 files, ~400 lines

Neither `InlineEdit` nor `InlineAdd` have any tests. These classes manage form container construction, default-value injection, submit handling, and event dispatching. The `FilterTest` incidentally exercises one side-effect of inline add form state, but `InlineEdit` and `InlineAdd` themselves are not tested at all.

### 4. `Column/MultiAction` and `Column/ItemDetail`

- `MultiAction` (143 lines): wraps a dropdown of `Action` objects with its own duplication checks and row-condition logic — no tests.
- `ItemDetail` (181 lines): supports three render modes (renderer / template / block), a form integration via `ItemDetailForm`, and render conditions — no tests.

### 5. `ColumnsSummary` — 162 lines

Accumulates per-column numeric summaries across rows, applies `ColumnNumber` formatting, and supports a custom renderer. Zero tests.

### 6. `DataModel` — 144 lines

`DataModel` is the central data source router: it inspects the raw source (array, Dibi Fluent, Nette Database Selection, Doctrine QueryBuilder, Nextras Collection, or `IDataSource`) and instantiates the appropriate driver. The driver-selection branches (including MSSQL/PostgreSQL detection for Dibi and Nette Database) are never exercised by tests.

### 7. `Status/Option` — 180 lines

`Option` manages icon, class, title, text, confirmation, and render-condition for status column options. `ColumnStatusTest` tests the outer `ColumnStatus` class but never touches `Option` directly.

---

## Massively Undertested Files

These files have some related tests, but coverage is thin relative to their size and complexity.

### `Datagrid.php` — 2,966 lines, 3 test methods

`DatagridTest.phpt` contains only:

1. `testDefaultFilter` — checks `isFilterDefault()`
2. `testResetFilterLinkWithRememberOption`
3. `testResetFilterLinkWithNoRememberOption`

The `Datagrid` class exposes over 100 public methods across column management, sorting, filtering, pagination, exports, inline editing, group actions, tree-view, column hiding, aggregation, and signal handlers. The vast majority is untested.

High-priority gaps within `Datagrid`:

- **Sorting** — `setDefaultSort`, `setSortable`, `setMultiSortEnabled`, `handleSort`, multi-column sort
- **Pagination** — `setItemsPerPageList` combined with actual data rendering, `handlePerPage`
- **Column management** — `addColumnText`, `addColumnLink`, `addColumnNumber`, `addColumnDateTime`, `addColumnStatus`, `removeColumn`, `addMultiAction`
- **Tree-view** — `setTreeView` and `treeViewChildrenCallback`
- **Column hiding** — `setColumnsHideable`, `handleShowColumn`, `handleHideColumn`
- **Inline editing** — `handleInlineEdit`, `handleInlineAdd`, related form processing
- **Group actions** — `addGroupAction`, `addGroupButtonAction`, `handleGroupAction`

### `Column/Column.php` — 567 lines

The base `Column` class has a large API covering sortability, templates, editable inline cells, alignment, fit-content, custom renderers, and header escaping. No dedicated test file exists. Behaviour is exercised only indirectly via column-subtype tests.

### `DataSource/ArrayDataSource.php` — 330 lines

`ArrayDataSourceTest.phpt` is 23 lines and only instantiates the class before delegating entirely to `BaseDataSourceTest`. The base test suite covers filtering and sorting fundamentals, but misses:

- `FilterDate` and `FilterDateRange` conditions
- `FilterMultiSelect` conditions
- Custom condition callbacks
- Sort callback (custom `callable` sort)

### `Export/Export.php` and `Export/ExportCsv.php` — 161 + ~80 lines

`ExportTest.phpt` (96 lines, 2 test methods) verifies that a callback export handler fires and that an exception is raised before a data source is set. It does not test:

- CSV export output (`ExportCsv`)
- `CsvResponse` HTTP response formatting
- `CsvDataModel` data extraction
- Export with column mapping and formatting

### `Filter/Filter.php` and filter subtypes — 177 + ~200 lines

`FilterTest.phpt` tests form submission mechanics but not the individual filter types. The following filter classes have no direct tests:

- `FilterDate` and `FilterDateRange`
- `FilterMultiSelect`
- `FilterSelect` (tested only as part of `BaseDataSourceTest`)

---

## Utility Classes — All Untested

| File | Lines | What it does |
|------|-------|--------------|
| `Utils/DateTimeHelper.php` | 80 | Parses date strings across multiple formats; throws `DatagridDateTimeHelperException` on failure |
| `Utils/ArraysHelper.php` | 55 | `testEmpty` (recursive) and `testTruthy` — used throughout filter logic |
| `Utils/Sorting.php` | 45 | Value object wrapping sort column/direction and an optional sort callback |
| `Utils/PropertyAccessHelper.php` | ~40 | Wraps Symfony PropertyAccess for nested property resolution |
| `Localization/SimpleTranslator.php` | ~50 | Minimal translator used in tests but never tested itself |

`DateTimeHelper` is worth prioritising: it handles seven different date formats plus a `strtotime` fallback, and incorrect parsing silently produces wrong dates in `ColumnDateTime`.

---

## Data Sources — Partial Coverage

| Data source | Test status |
|-------------|-------------|
| `ArrayDataSource` | Covered by `BaseDataSourceTest` (text, range, select, sort, limit, filterOne) |
| `DoctrineDataSource` | Covered (mock-based) |
| `DoctrineCollectionDataSource` | Covered (mock-based) |
| `NetteDatabaseTableDataSource` | Covered (mock-based) |
| `DibiFluentDataSource` | Covered (mock-based) |
| `NextrasDataSource` | Covered (mock-based) |
| `DibiFluentMssqlDataSource` | **No tests** |
| `DibiFluentPostgreDataSource` | **No tests** |
| `NetteDatabaseTableMssqlDataSource` | **No tests** |
| `ApiDataSource` | **No tests** |
| `ElasticsearchDataSource` | **No tests** |

The MSSQL and PostgreSQL variants differ from their base classes in LIMIT/OFFSET syntax, which is exactly the kind of subtle difference that breaks in production. `ApiDataSource` and `ElasticsearchDataSource` are entirely unexercised.

---

## Proposed Improvements (Prioritised)

### Priority 1 — High impact, feasible without external services

**1a. `Utils/DateTimeHelper` unit tests**
Write a `DateTimeHelperTest` covering each of the seven recognised formats, the `DateTime` and `DateTimeImmutable` pass-through paths, the `strtotime` fallback, and the exception on an unparseable string. This class is pure PHP with no dependencies and can be tested in isolation.

**1b. `Utils/ArraysHelper` unit tests**
`testEmpty` and `testTruthy` have non-trivial edge cases (the `0`, `'0'`, and `false` special cases in `testEmpty`; nested iterables in `testTruthy`). A dedicated `ArraysHelperTest` would take minimal effort and catch any future regressions.

**1c. `ColumnsSummary` unit tests**
Instantiate a grid with `ColumnNumber` columns, add rows via `add()`, and assert `render()` output. Also test the custom renderer path and the `setPositionTop` behaviour. No external dependencies needed.

**1d. `GroupAction` unit tests**
`GroupActionCollection::addToFormContainer` can be tested by constructing a real Nette `Form` and asserting the resulting form controls. Test all action subtypes (button, select, multi-select, text, textarea) and the `handleGroupAction` signal dispatch.

**1e. `Datagrid` sorting tests**
Expand `DatagridTest` with tests for:
- `setDefaultSort` applying the correct initial sort
- `handleSort` updating the sort state
- Multi-sort enabled/disabled behaviour
- `setSortable(false)` preventing sort signal handling

**1f. `Status/Option` unit tests**
`Option` has no dependencies beyond Nette Utils. Test each property setter and the render output in all confirmation modes.

### Priority 2 — Important logic, moderate effort

**2a. `InlineEdit` and `InlineAdd` tests**
These require constructing a form container and simulating submit. The existing `FilterTest::testFilterSubmitWithInvalidInlineAddOpen` shows the pattern. Extend to cover `onSubmit`, `onSetDefaults`, `setShowNonEditingColumns`, and the `positionTop` flag.

**2b. `AggregationFunction` tests**
`FunctionSum` with an `ArrayDataSource`-backed collection path can be tested without a real database. The trait's mutual-exclusion guards and `IAggregatable` requirement should also be asserted.

**2c. `MultiAction` tests**
Mirror the existing `ColumnActionTest` approach: construct a `MultiAction`, add child actions, and assert the rendered dropdown HTML. Cover the duplication exception and row-condition filtering.

**2d. `DataModel` data source routing tests**
`DataModel`'s constructor branch logic (array → ArrayDataSource, QueryBuilder → DoctrineDataSource, etc.) should be tested with unit-level mocks to ensure each source type is routed correctly, including the MSSQL/PostgreSQL detection paths.

**2e. `ExportCsv` and `CsvResponse` tests**
Extend `ExportTest` to exercise the CSV export path: add columns, set a data source, trigger `handleExport`, and assert the `CsvResponse` body matches the expected CSV content.

### Priority 3 — Lower urgency or requiring infrastructure

**3a. `ApiDataSource` tests**
These require HTTP mocking (e.g. a mock HTTP client or a test server). Consider adding a mock-based test that verifies the URL construction, filter serialisation, and response parsing.

**3b. `ElasticsearchDataSource` tests**
Similar to `ApiDataSource`, requires mocking the Elasticsearch client.

**3c. MSSQL/PostgreSQL data source variants**
`DibiFluentMssqlDataSource`, `DibiFluentPostgreDataSource`, and `NetteDatabaseTableMssqlDataSource` primarily differ in `LIMIT`/`OFFSET` and quoting syntax. Mock-based tests similar to the existing Dibi tests would be sufficient.

**3d. `Column/Column.php` sorting and editable cell tests**
The editable-cell API (`setEditableCallback`, `setEditableInputType`, `setEditableInputTypeSelect`) and the sort-next cycle (`getSortNext`) deserve dedicated tests separate from the column-subtype tests.

**3e. `Datagrid` tree-view and column-hiding tests**
These features involve stateful signal handlers and are harder to exercise without a full presenter context, but the existing `TestingDatagridFactoryRouter` infrastructure already sets up that environment.

---

## Summary Table

| Area | Files | Lines | Priority |
|------|-------|-------|----------|
| `Utils/DateTimeHelper` | 1 | 80 | P1 |
| `Utils/ArraysHelper` | 1 | 55 | P1 |
| `ColumnsSummary` | 1 | 162 | P1 |
| `GroupAction/` | 7 | ~450 | P1 |
| `Datagrid` sorting/pagination | 1 | 2,966 | P1 |
| `Status/Option` | 1 | 180 | P1 |
| `InlineEdit/` | 2 | ~400 | P2 |
| `AggregationFunction/` | 6 | ~250 | P2 |
| `Column/MultiAction` | 1 | 143 | P2 |
| `DataModel` routing | 1 | 144 | P2 |
| `Export/ExportCsv` + `CsvResponse` | 3 | ~320 | P2 |
| `ApiDataSource` | 1 | 130 | P3 |
| `ElasticsearchDataSource` | 1 | 191 | P3 |
| MSSQL/PostgreSQL data sources | 3 | ~200 | P3 |
| `Column/Column.php` editable/sort | 1 | 567 | P3 |
