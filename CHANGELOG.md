# Changelog

## [7.1.1] - 2025-09-01

### Added
- Added example of nested components when setting sortable handler in documentation

### Fixed
- Fixed missing constructor in SimpleTranslator class
- Fixed rollup configuration with $ symbol handling
- Updated FontAwesome prefix from deprecated `fa` to `fas` for v5 compatibility

### Changed
- Updated build and publish process for npm package

## [7.1.0] - 2025-08-04

### Added
- Introduced State Storage documentation and related sections
- Added possibility to deactivate Doctrine Paginator
- Added pluggable state storage system for session handling

### Changed
- **BREAKING**: Removed backwards compatibility in SimpleTranslator
- **BREAKING**: Dropped HappyInputs dependency
- Upgraded to Naja 3
- Downgraded PHP requirements to 8.2 (from 8.3)
- Switched from persistent annotation to attribute
- Improved Bootstrap 5 select design and TomSelect padding
- Updated class naming: `ublaboo-datagrid-th-form-inline` → `datagrid-th-form-inline`

### Fixed
- Fixed editable plugin functionality
- Fixed reset column filter without deleting all grid filters
- Fixed group actions after dropping HappyInputs
- Fixed perPage component exception handling
- Fixed namespace hint in tests

### Removed
- Removed Ublaboo class aliases
- Removed `datagrid-all` asset bundle (not needed)
- Removed console.log statements

## [7.0.0] - 2025-05-31

### Added
- **Bootstrap 5 support** with vanilla JavaScript
- **PHP 8.0+ support** (minimum version)
- TypeScript rewrite of assets with plugin system
- Tom Select integration (replacing bootstrap-select)
- Rollup bundling system with `datagrid-full` and `datagrid-all` assets
- Data attributes for InlineAdd and InlineEdit functionality
- Support for Nextras ORM 4
- Support for Nextras ORM/DBAL 5
- Support for Dibi 5.0+
- Elasticsearch data source improvements
- `setColumnsHideable()` method for column visibility control
- Custom action href setting capability
- Reset filter buttons functionality
- Tree view expand functionality for child nodes
- Conjunction search for text filters
- `onColumnShow` and `onColumnHide` event methods

### Changed
- **BREAKING**: Minimum PHP version raised to 8.1
- **BREAKING**: Namespace migration from `Ublaboo\DataGrid` to `Contributte\Datagrid` (with backwards compatibility aliases)
- **BREAKING**: Switched from `ITranslator` to `Translator` interface
- **BREAKING**: Replaced data-datagrid-`<name>` with data-datagrid-name="`<name>`"
- **BREAKING**: Changed `.datagrid` CSS selector to `[data-datagrid-name]`
- **BREAKING**: Bootstrap 4 → Bootstrap 5 migration (CSS classes: `text-right` → `text-end`, `input-sm` → `form-select-sm`, etc.)
- **BREAKING**: Upgraded minimum versions of doctrine, nette/utils, nette/forms, symfony components
- **BREAKING**: Dropped contributte/application dependency (CsvResponse copied to datagrid)
- **BREAKING**: Context → Explorer, getSupplementalDriver → getDriver in NetteDatabaseSelectionHelper
- **BREAKING**: Template engine changes for Latte strict mode compatibility
- **BREAKING**: Translation keys renamed from `ublaboo_datagrid.*` to `contributte_datagrid.*`
- Modernized CI workflows
- Assets cleanup and reorganization
- Improved ArrayDataSource sorting functionality
- Enhanced number formatting to handle large numbers and overflow
- Updated vanilla datepicker for Bootstrap 5 CSS
- Improved confirmation message handling

### Fixed
- Fixed "Attempt to read property on array" error in ArrayDataSource
- Fixed attribute name for sortable URL retrieval
- Fixed confirmation message cancel button functionality
- Fixed custom classes on checkbox in inline edit
- Fixed non-functional confirm dialog
- Fixed treeview expand child nodes
- Fixed multiple listeners on the same element
- Fixed filterRange() ignoring zero values in NetteDatabaseTableDataSource
- Fixed handleShowColumn crash when all columns are shown
- Fixed PHPStan issues and type errors
- Fixed inline edit and inline add plugins
- Fixed pagination buttons for Latte strict mode
- Fixed vanillajs-datepicker Bootstrap 5 CSS path
- Fixed ElasticsearchDataSource functionality

### Removed
- **BREAKING**: Removed bootstrap-select dependency
- **BREAKING**: Removed PHP 7.x support
- Removed .scrutinizer.yml and .travis.yml files
- Removed obsolete compatibility files
- Removed dependabot configuration

### Security
- Updated all dependencies to secure versions
- Improved type safety with PHP 8+ features

### Deprecated
- Various legacy methods and classes (see upgrade guide for details)

## [6.10.0] - 2023-XX-XX

### Added
- Final stable release of 6.x series
- Various bug fixes and improvements

---

## Upgrade Guide

### Upgrading from 6.x to 7.0

#### PHP Version
- **Required**: Update to PHP 8.1 or higher
- Update your `composer.json` to require `"php": ">=8.1"`

#### Namespace Migration
- Update all class imports from `Ublaboo\DataGrid` to `Contributte\Datagrid`
- Backwards compatibility aliases are included but will be removed in v8.0
- Update composer package name if using custom autoloading

#### Bootstrap 4 → Bootstrap 5
- Update your Bootstrap CSS/JS to version 5.x
- Review custom CSS that may rely on Bootstrap 4 classes

#### Asset Changes
- Replace bootstrap-select with Tom Select
- Update asset imports if using custom builds
- Use new bundled assets: `datagrid-full` for complete functionality

#### Template Changes
- Update data attributes: `data-datagrid-<name>` → `data-datagrid-name="<name>"`
- Update CSS selectors: `.datagrid` → `[data-datagrid-name]`
- Update Bootstrap 4 classes to Bootstrap 5:
  - `text-right` → `text-end`
  - `text-left` → `text-start`
  - `input-sm` → `form-select-sm`
  - `data-toggle` → `data-bs-toggle`
  - Update input group structures (remove `input-group-append`)

#### Interface Changes
- Update translator usage from `ITranslator` to `Translator`
- Remove usage of contributte/application CsvResponse (now internal)
- Update translation keys from `ublaboo_datagrid.*` to `contributte_datagrid.*`

#### Data Source Updates
- Update Nextras ORM to version 4+ or 5+
- Update Doctrine to supported versions
- Review custom data source implementations

### Upgrading from 7.0 to 7.1

#### Session Handling
- The session handling system has been refactored to support pluggable state storage
- If you have custom session handling, review the new state storage interface

#### SimpleTranslator
- Backwards compatibility has been removed
- Review your translator implementation for breaking changes

#### Dependencies
- HappyInputs dependency has been removed
- Update to Naja 3 if using AJAX functionality

### Upgrading from 7.1.0 to 7.1.1

#### FontAwesome
- Update FontAwesome icon prefixes from `fa` to `fas` for v5 compatibility
- No breaking changes in this release

#### SimpleTranslator
- If extending SimpleTranslator, note the new constructor that accepts a custom dictionary
