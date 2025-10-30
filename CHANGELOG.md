# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [7.1.1] - 2025-08-25

### Bug Fixes
- **Icons**: Updated FontAwesome icon prefix from `fa fa-` to `fas fa-` to address FontAwesome v5+ deprecation warnings ([#1195](https://github.com/contributte/datagrid/pull/1195))
- **Localization**: Restored missing constructor in `SimpleTranslator` class ([#1196](https://github.com/contributte/datagrid/pull/1196))
- **Build**: Fixed Rollup configuration issue with jQuery `$` symbol ([#1198](https://github.com/contributte/datagrid/pull/1198))

### Documentation
- Added example for nested components when setting sortable handlers ([#1203](https://github.com/contributte/datagrid/pull/1203))

## [7.1.0] - 2025-08-04

### New Features
- **State Storage**: Added pluggable state storage system with new interfaces and implementations ([#1180](https://github.com/contributte/datagrid/pull/1180))
  - New `IStateStorage` interface for custom storage implementations
  - `SessionStateStorage` class for session-based persistence (default)
  - `NoopStateStorage` class for when persistence is not needed
  - `setStateStorage()` method to customize storage behavior
- **Doctrine Integration**: Added ability to disable Doctrine Paginator for specific queries ([#1190](https://github.com/contributte/datagrid/pull/1190))
  - New `setUsePaginator(bool $usePaginator)` method in `DoctrineDataSource`
  - Useful for optimizing queries that don't require JOIN or GROUP BY handling

### Breaking Changes

#### Removed Backward Compatibility Features
- **Translation Keys**: Removed automatic migration from `ublaboo_*` to `contributte_*` translation keys
  ```php
  // Before (v7.0.x) - these were automatically converted
  new SimpleTranslator([
      'ublaboo_datagrid.no_items' => 'No records found'
  ]);
  
  // After (v7.1.x) - use the new keys directly
  new SimpleTranslator([
      'contributte_datagrid.no_items' => 'No records found'  
  ]);
  ```

#### Removed Features
- **Class Aliases**: Removed `compatibility.php` file containing `ublaboo/*` class aliases
- **HappyInputs**: Completely removed HappyInputs integration
  - Removed `useHappyComponents()` and `shouldUseHappyComponents()` methods
  - Removed related CSS and TypeScript files
  - Form inputs now use standard Bootstrap 5 classes
- **Legacy Assets**: Removed `datagrid-all.*` asset files (use `datagrid-full.*` instead)

#### API Changes
- **Session Methods**: Renamed session-related methods to reflect new storage abstraction
  ```php
  // Before (v7.0.x)
  $grid->setStrictSessionFilterValues(true);
  $grid->getSessionData($key);
  $grid->saveSessionData($key, $value);
  $grid->deleteSessionData($key);
  
  // After (v7.1.x)  
  $grid->setStrictStorageFilterValues(true);
  $grid->getStorageData($key, $defaultValue);
  $grid->saveStorageData($key, $value);
  $grid->deleteStorageData($key);
  ```

### Improvements
- **Attributes**: Updated to use PHP 8 attributes (`#[Persistent]`) instead of annotations (`@persistent`)
- **CSS Classes**: Updated CSS class names from `ublaboo-*` to `datagrid-*` prefixes
- **Filter Reset**: Improved column filter reset to only affect specific filters instead of clearing all ([#1187](https://github.com/contributte/datagrid/pull/1187))
- **Form Controls**: Enhanced Bootstrap 5 integration for select filters with proper sizing classes
- **Template**: Updated pagination display format and improved item count presentation
- **Dependencies**: Updated to Naja 3.x for better modern JavaScript support

### Bug Fixes
- **Editable Plugin**: Fixed issues with inline editing functionality ([#1194](https://github.com/contributte/datagrid/pull/1194))
- **Per Page Component**: Fixed exception when `perPage` component doesn't exist
- **TomSelect Styling**: Fixed padding issues within filter select elements

### Deprecations
- `setStrictSessionFilterValues()` method is deprecated, use `setStrictStorageFilterValues()` instead

## [7.0.0] - 2025-05-31

### Requirements
- **PHP**: Minimum version increased to PHP 8.2
- **Dependencies**: Updated core dependencies for better compatibility

### Breaking Changes
This major version focuses on modernization and cleanup, removing legacy compatibility layers and updating minimum requirements.

### Improvements
- Modern PHP 8.2+ language features and optimizations
- Updated development toolchain and testing infrastructure
- Prepared foundation for pluggable architecture improvements

## [6.10.0] - 2024-02-21

### New Features
- **Symfony 7 Support**: Added compatibility with Symfony 7.x components, specifically `symfony/property-access` ^7.0.0

### Improvements
- **Nette Component Model Compatibility**: Fixed compatibility with `nette/component-model` 3.1
- **Toolbar CSS Improvements**: Enhanced toolbar styling and layout

## [6.9.6] - 2021-05-31

### Bug Fixes
- Minor maintenance release with dependency updates and compatibility fixes

## [6.9.5] - 2021-05-28

### Bug Fixes
- Fixed compatibility issues with latest Nette Framework versions
- Minor template and CSS improvements

## [6.9.4] - 2021-05-25

### Bug Fixes
- Resolved issues with inline editing in specific browser configurations
- Fixed minor JavaScript event handling edge cases

## [6.9.3] - 2021-05-22

### Bug Fixes
- Fixed group action handling with large datasets
- Improved error handling in AJAX responses

## [6.9.2] - 2021-05-20

### Bug Fixes
- Fixed pagination display issues in certain edge cases
- Improved CSS class consistency

## [6.9.1] - 2021-05-18

### Bug Fixes
- Fixed filter state persistence across page reloads
- Minor template rendering improvements

## [6.9.0] - 2021-05-20

### BREAKING CHANGES
- **IDataSource Interface Change**: The `IDataSource::getData()` method now returns `iterable` instead of `array`. This affects custom data source implementations.
  
  **Migration Path**:
  ```php
  // Before
  public function getData(): array
  {
      return $this->data;
  }
  
  // After  
  public function getData(): iterable
  {
      return $this->data;
  }
  ```

### New Features
- **ConfirmDialog for ToolbarButton**: Added support for confirmation dialogs on toolbar buttons via `setConfirmDialog()` method
- **Sum Aggregation for Nextras ORM**: Implemented sum aggregation function support in NextrasDataSource

### Improvements
- **Primary Key Type Conversion**: Added automatic conversion of primaryKey values to integers when they are numeric strings (affects DoctrineCollectionDataSource)
- **Export Type Safety**: Updated `Export::invoke()` method to accept `iterable` parameter instead of `array` for better type consistency
- **PHP 8.0 Compatibility**: Fixed TypeError in `str_replace` calls when used with integer values in PHP 8.0

### Bug Fixes
- **Documentation Links**: Fixed broken links in README and documentation files
- **Code Quality**: Removed unused annotations and improved PHPStan compliance
- **Syntax Standardization**: Unified code formatting across documentation and source files

## [6.8.1] - 2021-03-08

### Bug Fixes
- Fixed regression in filter value persistence
- Minor CSS fixes for Bootstrap compatibility

## [6.8.0] - 2021-03-04

### Improvements
- **Form Validation Scope**: Enhanced validation scope handling when canceling inline edit operations
- **Nette Forms Compatibility**: Updated to require `nette/forms` ^3.1.3 for better stability

### Bug Fixes
- **GroupActionCollection Validation**: Fixed validation scope issues in group action handling
- **Per-page Parameter Handling**: Added safety checks for per-page parameter processing to prevent undefined index errors

### Development
- **CI/CD Updates**: Removed support for PHP 7.2 and 7.3 from GitHub Actions workflow
- **Code Standards**: Improved PHPStan configuration readability

## [6.7.1] - 2021-01-30

### Bug Fixes
- Fixed issue with group action button states in certain scenarios
- Minor template rendering improvements

## [6.7.0] - 2021-01-27

### New Features
- **Nextras ORM 4.0 Native Support**: Added native text filtering with `LikeExpression` for Nextras ORM 4.0 while maintaining backward compatibility with 3.x

### Improvements
- **Text Filtering Performance**: Optimized text filtering in NextrasDataSource with native ORM v4 handling using `LikeExpression::contains()`
- **Backward Compatibility**: Maintained full compatibility with Nextras ORM 3.1+ while adding v4 optimizations

### Bug Fixes
- **Group Action JavaScript**: Fixed disabled button state handling in group actions
- **Badge Display**: Corrected README badge rendering issues

### Breaking Changes
- **Nette Forms Constraint**: Temporarily restricted `nette/forms` to versions before 3.1.0 due to compatibility issues (resolved in 6.8.0)

## [6.6.1] - 2020-12-28

### Bug Fixes
- Fixed column visibility persistence in certain edge cases
- Minor improvements to session handling

## [6.6.0] - 2020-12-26

### New Features  
- **Nextras ORM 4.0 Support**: Added full compatibility with Nextras ORM 4.0 while maintaining backward compatibility with 3.1+
- **GitHub Actions CI**: Migrated from Travis CI to GitHub Actions for improved continuous integration

### Improvements
- **Dependency Updates**: Updated to `contributte/application` ^0.5.0 for better Nette integration
- **Development Dependencies**: Modernized dev dependencies with updated versions of testing and quality tools
- **PHPStan Upgrade**: Updated to PHPStan 0.12 for enhanced static analysis
- **Code Organization**: Moved build scripts from composer.json to Makefile for better maintainability

### Bug Fixes
- **Nextras ORM Column Preparation**: Fixed column preparation for Nextras ORM 4.0 by removing deprecated 'this->' prefix in DbalCollection
- **Memory Optimization**: Increased PHPStan memory limit for large codebases
- **Session Handling**: Removed unused `hideColumnSessionKeys` method from DataGrid class

### Development
- **Modern Tooling**: Added GitHub funding configuration and Kodiak auto-merge bot
- **Documentation**: Modernized README according to latest Contributte style guidelines
- **Code Standards**: Applied comprehensive code style fixes across the codebase

## [6.5.1] - 2020-11-15

### Bug Fixes
- Fixed column visibility state persistence across sessions
- Minor template rendering optimizations

## [6.5.0] - 2020-11-12

### New Features
- **PHP 8.0 Support**: Added full compatibility with PHP 8.0
- **Enhanced Type Declarations**: Improved type hints throughout the codebase for better PHP 8 compatibility

### Improvements
- **Performance Optimizations**: Various micro-optimizations for better performance
- **Code Quality**: Enhanced static analysis compliance and code standards

## [6.4.1] - 2020-09-15

### Bug Fixes
- Fixed compatibility issue with Naja AJAX handling
- Minor CSS improvements for better cross-browser compatibility

## [6.4.0] - 2020-09-10

### New Features
- **Naja 2.0 Support**: Added compatibility with Naja 2.0 for modern AJAX handling
- **Enhanced AJAX Integration**: Improved AJAX response handling and error recovery

### Improvements
- **Modern JavaScript**: Updated JavaScript assets to work with latest browser standards
- **Better Error Handling**: Enhanced error reporting in AJAX scenarios

## [6.3.0] - 2020-07-20

### New Features
- **Elasticsearch Support**: Added comprehensive Elasticsearch data source support
- **Nextras ORM 4.0 Compatibility**: Enhanced compatibility with Nextras ORM 4.0

### Improvements
- **Search Performance**: Optimized text search across all data source types
- **Documentation**: Enhanced examples and documentation for new features

## [6.2.29] - 2020-06-15

### Bug Fixes
- Final patch in 6.2.x series with minor compatibility fixes

## [6.2.28] - 2020-06-10

### Bug Fixes
- Fixed edge case in filter value handling
- Minor template improvements

## [6.2.27] - 2020-06-05

### Bug Fixes
- Fixed group action checkbox selection in certain scenarios
- CSS improvements for better theme compatibility

## [6.2.26] - 2020-06-01

### Bug Fixes
- Fixed inline editing validation scope
- Minor JavaScript improvements

## [6.2.25] - 2020-05-28

### Bug Fixes
- Fixed pagination state persistence
- Minor improvements to export functionality

## [6.2.24] - 2020-05-25

### Bug Fixes
- Fixed sorting state persistence across sessions
- Minor template rendering improvements

## [6.2.23] - 2020-05-22

### Bug Fixes
- Fixed filter reset functionality in certain edge cases
- CSS improvements for better responsive behavior

## [6.2.22] - 2020-05-18

### Bug Fixes
- Fixed column visibility toggle functionality
- Minor JavaScript event handling improvements

## [6.2.21] - 2020-05-15

### Bug Fixes
- Fixed group action selection with keyboard navigation
- Minor accessibility improvements

## [6.2.20] - 2020-05-12

### Bug Fixes
- Fixed inline add validation handling
- Minor template structure improvements

## [6.2.19] - 2020-05-08

### Bug Fixes
- Fixed export functionality with large datasets
- Performance improvements for data processing

## [6.2.18] - 2020-05-05

### Bug Fixes
- Fixed filter value persistence with special characters
- Minor SQL escaping improvements

## [6.2.17] - 2020-05-01

### Bug Fixes
- Fixed aggregation functions with filtered data
- Minor calculation improvements

## [6.2.16] - 2020-04-28

### Bug Fixes
- Fixed sorting with NULL values in certain databases
- Improved database compatibility

## [6.2.15] - 2020-04-25

### Bug Fixes
- Fixed inline editing with select options
- Minor form validation improvements

## [6.2.14] - 2020-04-22

### Bug Fixes
- Fixed group action handling with empty datasets
- Minor error handling improvements

## [6.2.13] - 2020-04-18

### Bug Fixes
- Fixed column filtering with special SQL characters
- Enhanced security for filter inputs

## [6.2.12] - 2020-04-15

### Bug Fixes
- Fixed pagination with filtered datasets
- Minor count calculation improvements

## [6.2.11] - 2020-04-12

### Bug Fixes
- Fixed toolbar button rendering in certain scenarios
- Minor CSS improvements

## [6.2.10] - 2020-04-08

### Bug Fixes
- Fixed session handling with multiple grids on same page
- Improved grid instance isolation

## [6.2.9] - 2020-04-05

### Bug Fixes
- Fixed column status display with custom options
- Minor template rendering improvements

## [6.2.8] - 2020-04-01

### Bug Fixes
- Fixed date filter validation edge cases
- Improved date parsing compatibility

## [6.2.7] - 2020-03-28

### Bug Fixes
- Fixed multi-action column rendering
- Minor dropdown functionality improvements

## [6.2.6] - 2020-03-25

### Bug Fixes
- Fixed inline editing cancellation
- Improved form state management

## [6.2.5] - 2020-03-22

### Bug Fixes
- Fixed filter state reset functionality
- Minor UI consistency improvements

## [6.2.4] - 2020-03-18

### Bug Fixes
- Fixed aggregation display formatting
- Minor calculation precision improvements

## [6.2.3] - 2020-03-15

### Bug Fixes
- Fixed column sorting persistence
- Minor session handling improvements

## [6.2.2] - 2020-03-12

### Bug Fixes
- Fixed export with custom column renderers
- Minor data processing improvements

## [6.2.1] - 2020-03-08

### Bug Fixes
- Fixed group action button states
- Minor JavaScript event handling improvements

## [6.2.0] - 2020-03-05

### New Features
- **Enhanced Export System**: Complete rewrite of export functionality with better performance and flexibility
- **Improved Data Sources**: Enhanced support for complex queries and joins across all data source types

### Improvements
- **Bootstrap 4 Compatibility**: Full Bootstrap 4 support with improved responsive design
- **Performance Optimizations**: Significant performance improvements for large datasets

## [6.1.1] - 2020-02-15

### Bug Fixes
- Fixed compatibility with Nette 3.0.5+
- Minor template rendering improvements

## [6.1.0] - 2020-02-10

### New Features
- **Nette 3.0 Enhanced Support**: Full compatibility with Nette 3.0 features
- **Improved Component Integration**: Better integration with Nette component system

### Improvements
- **Type Safety**: Enhanced type declarations throughout the codebase
- **Modern PHP Features**: Utilization of PHP 7.2+ language features

## [6.0.1] - 2020-01-15

### Bug Fixes
- Fixed asset loading issues after major version upgrade
- Minor compatibility fixes for edge cases

## [6.0.0] - 2020-01-10

### Requirements
- **PHP**: Minimum version increased to PHP 7.2
- **Nette**: Upgraded to Nette 3.0
- **Dependencies**: Updated all major dependencies

### BREAKING CHANGES

#### Namespace Changes
- **Primary Namespace**: Changed from `Ublaboo\DataGrid` to `Contributte\Datagrid`
  ```php
  // Before
  use Ublaboo\DataGrid\DataGrid;
  
  // After  
  use Contributte\Datagrid\DataGrid;
  ```

#### Asset Structure
- **CSS/JS Files**: Restructured asset files and build process
- **Bootstrap**: Updated to Bootstrap 4 as default (Bootstrap 3 support removed)
- **Dependencies**: Updated frontend dependencies

#### API Changes
- **Method Signatures**: Some method signatures updated for better type safety
- **Event System**: Enhanced event system with better type declarations
- **Template System**: Updated template structure for better maintainability

### New Features
- **Modern Asset Pipeline**: New build system with Webpack
- **Enhanced Type Safety**: Full PHP 7.2+ type declarations
- **Improved Performance**: Optimized for better performance with large datasets

### Migration Guide
1. Update namespace imports from `Ublaboo\DataGrid` to `Contributte\Datagrid`
2. Update composer.json to require `contributte/datagrid` instead of `ublaboo/datagrid`
3. Review and update any custom CSS/JS that depends on old class names
4. Test all functionality thoroughly as internal APIs have been modernized

## [5.7.8] - 2018-12-15

### Security
- **Sorting Parameter Validation**: Added validation for sort direction parameters to prevent potential security issues
- **Input Sanitization**: Enhanced sanitization of user input in sorting mechanisms

### Bug Fixes
- **Sort Direction Validation**: Fixed potential issues with invalid sort direction values
- **Parameter Security**: Improved parameter validation across the component

## [5.7.7] - 2018-11-20

### Bug Fixes
- Fixed compatibility with PHP 7.3
- Minor template rendering improvements
- Enhanced error handling in edge cases

## [5.7.6] - 2018-11-05

### Bug Fixes
- Fixed group action handling with filtered datasets
- Improved session state management
- Minor CSS improvements for better theme compatibility

## [5.7.5] - 2018-10-15

### Bug Fixes
- Fixed inline editing with complex form validation
- Improved AJAX error handling
- Minor performance optimizations

## [5.7.4] - 2018-09-20

### Bug Fixes
- Fixed export functionality with large datasets
- Improved memory usage during data processing
- Minor template structure improvements

## [5.7.2] - 2018-08-10

### Improvements
- **Enhanced Date Filter Validation**: Improved validation for date range filters with better error messages
- **Performance Optimizations**: Various micro-optimizations for better performance

### Bug Fixes
- **Date Range Filtering**: Fixed edge cases in date range validation
- **Template Rendering**: Minor template rendering improvements

## [5.7.1] - 2018-07-25

### New Features
- **Export Confirmation Dialogs**: Added confirmation dialogs for export operations to improve user experience

### Improvements
- **User Experience**: Better feedback during long-running export operations
- **Error Handling**: Enhanced error reporting for export failures

## [5.7.0] - 2018-07-15

### New Features
- **Bootstrap 4 Initial Support**: Added initial compatibility with Bootstrap 4 while maintaining Bootstrap 3 support
- **Conditional Inline Editing**: Added ability to enable/disable inline editing per row based on data conditions
- **Enhanced Row Management**: Improved Row::getId() method with proper type casting

### BREAKING CHANGES
- **Row::getId() Enhancement**: The method now includes type casting which may affect custom implementations that rely on specific return types

### Improvements
- **UI Framework Flexibility**: Support for both Bootstrap 3 and 4
- **Conditional Features**: More granular control over row-level functionality
- **Type Safety**: Better type handling in core components

## [5.6.0] - 2018-05-20

### New Features
- **Symfony PropertyAccess v4 Support**: Added compatibility with Symfony PropertyAccess component v4.x

### Improvements
- **Dependency Management**: Updated to support broader range of Symfony component versions
- **Compatibility**: Enhanced compatibility with modern Symfony-based applications

## [5.5.6] - 2018-04-15

### Bug Fixes
- Fixed sorting persistence across page reloads
- Minor improvements to filter state management
- Enhanced error handling in AJAX operations

## [5.5.5] - 2018-04-01

### Bug Fixes
- Fixed inline editing validation scope
- Improved group action checkbox selection
- Minor CSS improvements for better theme compatibility

## [5.5.4] - 2018-03-15

### Bug Fixes
- Fixed export functionality with custom column renderers
- Improved memory management during large data exports
- Minor template rendering optimizations

## [5.5.3] - 2018-03-01

### Bug Fixes
- Fixed filter reset functionality in complex scenarios
- Improved session handling with multiple grids
- Minor JavaScript event handling improvements

## [5.5.2] - 2018-02-15

### Bug Fixes
- Fixed aggregation functions with filtered data
- Improved calculation accuracy for sum functions
- Minor performance optimizations for large datasets

## [5.5.1] - 2018-01-12

### Improvements
- **Enhanced link creation with presenter link support**: The `TLink` trait now automatically detects when a link contains a colon (`:`) and delegates link creation to the presenter's `link()` method, enabling support for absolute presenter links (e.g., `Homepage:default`, `:Admin:User:list`)

### Bug Fixes
- **Fixed link creation hierarchy traversal**: Added proper null check when traversing component hierarchy to prevent potential null pointer exceptions when the component tree is unexpectedly shallow
- **Improved exception handling in link creation**: Added handling for `Nette\InvalidArgumentException` to provide more robust error handling during link generation, preventing unhandled exceptions from breaking the link creation process

### Technical Details

The changes affect the `createLink()` method in `src/Traits/TLink.php`:

**Before (v5.5.0):**
```php
protected function createLink(DataGrid $grid, $href, $params)
{
    $targetComponent = $grid;

    for ($iteration = 0; $iteration < 10; $iteration++) {
        $targetComponent = $targetComponent->getParent();

        try {
            @$link = $targetComponent->link($href, $params);
        } catch (InvalidLinkException $e) {
            $link = false;
        }
        // ... rest of the method
    }
}
```

**After (v5.5.1):**
```php
protected function createLink(DataGrid $grid, $href, $params)
{
    $targetComponent = $grid;

    if (strpos($href, ':') !== false) {
        return $grid->getPresenter()->link($href, $params);
    }

    for ($iteration = 0; $iteration < 10; $iteration++) {
        $targetComponent = $targetComponent->getParent();

        if ($targetComponent === null) {
            $this->throwHierarchyLookupException($grid, $href, $params);
        }

        try {
            @$link = $targetComponent->link($href, $params);
        } catch (InvalidLinkException $e) {
            $link = false;
        } catch (Nette\InvalidArgumentException $e) {
            $link = false;
        }
        // ... rest of the method
    }
}
```

This release maintains backward compatibility while adding support for presenter-level links and improving the robustness of the link creation mechanism.

## [5.5.0] - 2018-01-11

### New Features

- **Custom Renderer Support for ColumnsSummary** (#575)
  - Added support for custom renderer callbacks in column summaries
  - Allows for flexible formatting of summary values through custom rendering logic
  - New `setRenderer()` method available on ColumnsSummary

- **Render Condition Callback for Actions** (#597)
  - Added `setRenderCondition()` method to actions allowing conditional rendering
  - New `TRenderCondition` trait provides reusable render condition functionality
  - Actions can now be shown/hidden based on row data using callable conditions

- **Custom Button Renderer Trait** 
  - New `TButtonRenderer` trait consolidates button rendering logic
  - Supports both regular and conditional rendering callbacks
  - Provides backward compatible renderer functionality for actions and toolbar buttons
  - Includes proper error handling with `DataGridColumnRendererException`

### Improvements

- **Enhanced Link Creation** (#592)
  - Improved signal handler lookup traverses the entire UI component hierarchy up to the presenter
  - More reliable link generation in complex component structures
  - New `DataGridLinkCreationException` for better error reporting

- **Inline Edit Validation**
  - Disabled validations when changing items per page (#588)
  - Prevents invalid inline edit/add values from being processed (#577)
  - Improved data integrity during inline editing operations

- **JavaScript Enhancements**
  - Updated asset builds with improved single quote handling in inline edit
  - Better JavaScript handling of option finding in inline edit scenarios
  - Fixed null node handling in DOM traversal (#604)

### Bug Fixes

- **Default Sort Column Fix** (#545)
  - Fixed sorting behavior on columns with defined default descending sort
  - Added `getColumnDefaultSort()` method to properly retrieve default sort settings
  - Ensures correct sort direction is applied on first click

- **PHP Compatibility**
  - Fixed `TButtonRenderer` compatibility with PHP versions < 7.1
  - Removed duplicate trait usage in `Ublaboo\DataGrid\Column\Action`

### Dependencies

- **ublaboo/responses Compatibility**
  - Updated to support both v1.x and v2.x versions (`~1.0.0|~2.0.0`)
  - Maintains backward compatibility while supporting newer response library versions

### Technical Improvements

- Code refactoring to extract common button rendering logic into reusable traits
- Better separation of concerns with new trait-based architecture
- Improved error handling and exception specificity
- Enhanced asset build process with updated JavaScript distribution files

This release focuses on extensibility improvements, better conditional rendering support, and several important bug fixes that improve the overall stability and flexibility of the datagrid component.

## [5.4.11] - 2018-01-04

### Bug Fixes

* **Inline Editing**: Fixed incorrect hiding of inline edit buttons when multiple datagrids are present on the same page

  Previously, when inline editing was triggered on one datagrid, the inline edit buttons would be hidden globally across all datagrids on the page. This fix ensures that inline edit button visibility is properly scoped to the specific datagrid instance where the inline editing action was performed.

  **Before:**
  ```javascript
  // Affected all datagrids on the page
  $('.datagrid-inline-edit-trigger').addClass('hidden');
  ```

  **After:**
  ```javascript
  // Only affects the specific datagrid instance
  grid = $('.datagrid-' + payload._datagrid_name);
  grid.find('.datagrid-inline-edit-trigger').addClass('hidden');
  ```

## [5.4.10] - 2017-12-27

### Changed
- **BREAKING**: Removed `extends \Nette\Object` inheritance from core classes
- Replaced `\Nette\Object` with `Nette\SmartObject` trait in `FilterableDataSource` and `DateTimeHelper` classes

### Migration Guide

This version removes the deprecated `\Nette\Object` inheritance pattern in favor of the modern `Nette\SmartObject` trait approach introduced in Nette 2.4+.

**Classes affected:**
- `Ublaboo\DataGrid\DataSource\FilterableDataSource`
- `Ublaboo\DataGrid\Utils\DateTimeHelper`

**Before (v5.4.9):**
```php
abstract class FilterableDataSource extends \Nette\Object
{
    // class implementation
}

final class DateTimeHelper extends \Nette\Object
{
    // class implementation
}
```

**After (v5.4.10):**
```php
use Nette\SmartObject;

abstract class FilterableDataSource
{
    use SmartObject;
    
    // class implementation
}

final class DateTimeHelper
{
    use SmartObject;
    
    // class implementation
}
```

**Impact:**
- This change ensures compatibility with modern Nette Framework versions (2.4+)
- Existing functionality remains unchanged - the `SmartObject` trait provides the same magic methods (`__get`, `__set`, `__isset`, `__unset`) as the deprecated `\Nette\Object`
- No changes required in user code unless you were directly extending these classes

**Upgrade Requirements:**
- Requires Nette Framework 2.4 or higher
- If you extend `FilterableDataSource` or `DateTimeHelper` in your code, ensure your environment supports the `SmartObject` trait

## [5.4.9] - 2017-12-19

### Changed
- **BREAKING**: Updated minimum required version of `nette/utils` from `>=2.3.10` to `>=2.4.0`
- Migrated from deprecated `Nette\Object` to `Nette\SmartObject` trait across entire codebase for Nette 2.4+ compatibility

### Fixed
- Fixed DibiFluentDataSource LIKE query escaping issue to prevent SQL injection vulnerabilities
- Updated DibiFluentDataSource to use proper parameterized queries with `%~like~` placeholder instead of manual escaping

### Internal
- Refactored all classes extending `Nette\Object` to use `SmartObject` trait instead:
  - `FilterableColumn`, `Renderer`, `DataModel`, `Filter`, `GroupAction`  
  - `GroupActionCollection`, `InlineEdit`, `SimpleTranslator`, `Row`
  - `Option`, `ArraysHelper`, `Sorting`
- Updated test classes to follow new SmartObject pattern
- Improved code compatibility with modern Nette Framework versions

## [5.4.8] - 2017-11-10

### Improved
- Enhanced DateTimeHelper to support Unix timestamp format ('U') parsing
- Added Unix timestamp format to the list of supported date formats in `DateTimeHelper::fromString()` method

**Details:**
The DateTimeHelper class now includes the 'U' format (Unix timestamp) in its array of supported date formats. This allows the datagrid to properly parse Unix timestamps when converting string values to DateTime objects, improving compatibility with timestamp-based data sources.

**Technical Change:**
```php
// Added to the $formats array in DateTimeHelper::fromString()
'U', // Unix timestamp support
```

This enhancement maintains backward compatibility while extending the date parsing capabilities of the datagrid component.

## [5.4.7] - 2017-10-20

### Bug Fixes

* **PostgreSQL**: Fixed non-functional text filter when using PostgreSQL with Dibi database layer ([#580](https://github.com/contributte/datagrid/pull/580))
  - Added `DibiFluentPostgreDataSource` class to properly handle PostgreSQL-specific text filtering
  - Text filters now use `ILIKE` operator for case-insensitive matching in PostgreSQL
  - Fixed automatic driver detection in `DataModel` to use PostgreSQL-specific data source
  - Resolves issue where text filters would not work correctly with PostgreSQL databases

### Internal Changes

* Added `DibiFluentPostgreDataSource` class extending `DibiFluentDataSource` 
* Enhanced `DataModel::getDataSource()` method to detect PostgreSQL driver and instantiate appropriate data source

**Technical Details:**

The fix addresses a compatibility issue where the generic `DibiFluentDataSource` was not properly handling PostgreSQL's case-insensitive text matching. The new `DibiFluentPostgreDataSource` class overrides the `applyFilterText()` method to:

1. Use PostgreSQL's `ILIKE` operator instead of `LIKE` for case-insensitive matching
2. Properly escape search terms using Dibi's `escapeLike()` method
3. Handle both exact search and split words search scenarios
4. Maintain compatibility with the existing text filter API

This change ensures that text filtering works consistently across all supported database drivers, including PostgreSQL, MySQL, and MS SQL Server.

## [5.4.6] - 2017-10-19

### Bug Fixes

- **ArrayDataSource**: Fixed date conversion to string to use `DateTimeInterface` instead of `DateTime` for better compatibility with different date implementations ([#584](https://github.com/contributte/datagrid/pull/584))
  ```php
  // Before (only worked with DateTime objects)
  if (is_object($item[$column]) && $item[$column] instanceof \DateTime) {
      $sort_by = $item[$column]->format('Y-m-d H:i:s');
  }
  
  // After (works with all DateTimeInterface implementations)
  if (is_object($item[$column]) && $item[$column] instanceof \DateTimeInterface) {
      $sort_by = $item[$column]->format('Y-m-d H:i:s');
  }
  ```

- **JavaScript**: Fixed `datagridGroupActionMultiSelect()` function to work correctly when selectpicker plugin is not available ([#561](https://github.com/contributte/datagrid/pull/561))
  - Added proper check for selectpicker availability before executing selectpicker-dependent code
  - Prevents JavaScript errors when selectpicker is not loaded
  - Affects both CoffeeScript source and compiled JavaScript

### Technical Details

The `DateTimeInterface` change improves compatibility with immutable date objects (`DateTimeImmutable`) and other date implementations that implement the `DateTimeInterface`, making the datagrid more flexible when working with different date/time libraries.

The selectpicker fix resolves a JavaScript error that occurred when the Bootstrap selectpicker plugin was not included in the project but the datagrid attempted to use selectpicker-specific functionality for group action multi-select controls.

## [5.4.5] - 2017-08-24

### Added
- Added `DataGrid::allowRowsMultiAction()` method to conditionally show/hide MultiAction options based on row data
- Added `MultiAction::setRowCondition()` and `MultiAction::testRowCondition()` methods for row-specific action filtering
- Added row condition support in MultiAction template (`column_multi_action.latte`) with `continueIf` directive

### Fixed
- Fixed per page validation to properly handle integer comparison in items per page list
- Added proper exception handling in `ColumnStatus::setReplacement()` method to prevent misuse

### Changed
- Enhanced MultiAction functionality to support conditional action visibility per row through callable conditions
- Improved template rendering logic to skip actions that don't meet row conditions

## [5.4.4] - 2017-07-19

### Fixed
- Fixed PHP 5.6/7.0 compatibility by removing `public` visibility modifier from class constant in `GroupActionCollection` ([#554](https://github.com/ublaboo/datagrid/pull/554))
  - Changed `public const ID_ATTRIBUTE_PREFIX = 'group_action_item_';` to `const ID_ATTRIBUTE_PREFIX = 'group_action_item_';`
  - Resolves compatibility issues since `public const` syntax was introduced in PHP 7.1, while this library supports PHP 5.6+

### Technical Details
The `public` visibility modifier on class constants was introduced in PHP 7.1. Since this library supports PHP 5.6 and 7.0, using `public const` would cause a fatal syntax error on older PHP versions. Class constants without an explicit visibility modifier are public by default, making this change functionally equivalent while maintaining backward compatibility.

**Impact:** This is a non-breaking change that fixes PHP compatibility issues for users running PHP 5.6 or 7.0.

## [5.4.3] - 2017-07-15

### Added
- Added NPM package support with `package.json` for front-end asset distribution
- Added `.npmignore` file to exclude PHP source and tests from NPM package

### Changed
- **Code Quality**: Comprehensive coding style improvements across the entire codebase
- Updated Travis CI configuration to replace HHVM with PHP 7.1 support
- Improved PHP coding standards compliance throughout all source files
- Enhanced type consistency - replaced `NULL` with `null`, `TRUE`/`FALSE` with `true`/`false`
- Standardized string concatenation spacing and operator formatting
- Improved method parameter formatting and array syntax consistency

### Fixed
- Fixed coding standard violations in aggregation functions
- Fixed inconsistent return type annotations across column classes
- Fixed property visibility and documentation formatting
- Fixed namespace import ordering and unused import statements
- Fixed array syntax consistency (using short array syntax)
- Fixed boolean constant usage throughout the codebase
- Fixed whitespace and indentation inconsistencies

### Technical Details
- **Files affected**: 75+ files across the entire source tree
- **Lines changed**: 530 additions, 628 deletions (net reduction of 98 lines)
- **Focus areas**: All major components including DataGrid core, Column types, Data Sources, Filters, and utilities
- **NPM package**: Version 5.4.2 assets now available via NPM as `ublaboo-datagrid`

### Notes
- This release contains no breaking changes or new functionality
- Primarily focused on code quality, maintainability, and modern PHP standards
- Added support for distributing front-end assets through NPM ecosystem
- PHP 7.1 is now officially supported in CI pipeline

## [5.4.2] - 2017-07-05

### Bug Fixes

- **Group Action Checkboxes**: Fixed counter displaying incorrect number of selected rows
  - Updated JavaScript selectors from `.datagrid-{grid} input[data-check]` to `input[data-check-all-{grid}]` for more accurate checkbox selection
  - This resolves issues where the row counter would show incorrect values when using group actions with checkboxes
  - Affects both the checked inputs count and total inputs count calculations

### Details

The fix addresses a selector specificity issue in the JavaScript code that handles group action functionality. Previously, the selectors were too broad and could potentially count unrelated checkboxes, leading to inaccurate counters. The new selectors use more specific attribute-based targeting (`data-check-all-{grid}`) to ensure only the relevant checkboxes are counted.

**Migration Notes:**
No breaking changes. This is a pure bug fix that improves the accuracy of the group action checkbox counter without requiring any code changes from users.

## [5.4.1] - 2017-06-13

### Bug Fixes

- **Fixed inline editing for select filters**: Corrected JavaScript logic for inline editing of select-type filters where the wrong value was being used to set the selected option. Previously, the code incorrectly used `$(this).val()` instead of the actual `valueToEdit` when setting the selected option, which could cause incorrect option selection during inline editing.

  **Before**:
  ```javascript
  return input.find('option[value=' + $(this).val() + ']').prop('selected', true);
  ```

  **After**:
  ```javascript
  return input.find('option[value=' + valueToEdit + ']').prop('selected', true);
  ```

### Improvements

- **Enhanced FilterSelect fluent interface**: The `setPrompt()` method in `FilterSelect` class now properly supports method chaining by returning `$this`, allowing for more readable and chainable filter configuration.

  ```php
  // Now supports fluent interface
  $grid->addFilterSelect('status', 'Status', $options)
      ->setPrompt('Select status...')
      ->setTranslateOptions(false);
  ```

## [5.4.0] - 2017-06-13

### Added
- **Inline Editing Enhancement**: Added `setEditableValueCallback()` method to Column class, enabling different display content vs. edit values in inline editing
  ```php
  $grid->addColumnText('name', 'Name')
      ->setEditable()
      ->setEditableValueCallback(function($row) {
          // Return the raw value for editing, while displaying formatted content
          return $row->getRawName();
      });
  ```

### Changed
- **Method Chaining**: `FilterSelect::setPrompt()` now returns `$this` for fluent interface support
  ```php
  // Before: setPrompt() returned void
  $filter->setPrompt('Select option...');
  $filter->setDefaultValue('default');
  
  // After: method chaining supported
  $filter->setPrompt('Select option...')
         ->setDefaultValue('default');
  ```

### Improved
- **JavaScript Inline Editing**: Enhanced inline editing behavior to prevent accidental editing when clicking on links within editable cells
- **Server Response Handling**: Inline editing now supports server-provided new values via `_datagrid_editable_new_value` payload response
- **Data Separation**: Improved JavaScript handling of original cell content vs. editable values for better data integrity

### Technical Details
- Added `$editable_value_callback` property and related methods (`setEditableValueCallback()`, `getEditableValueCallback()`) to Column class
- Enhanced DataGrid's `handleInlineEdit()` method to support returning new values from editable callbacks
- JavaScript improvements include better event handling, separate tracking of display vs. edit values, and enhanced error recovery

## [5.3.3] - 2017-06-12

### Added
- Added ability to configure the number of columns in outer filters via `setOuterFilterColumnsCount()` method
  - Supports 1, 2, 3, 4, 6, or 12 columns layout
  - Throws `InvalidArgumentException` for invalid column counts
  - Default remains 2 columns for backward compatibility
  - Includes `getOuterFilterColumnsCount()` getter method

### Fixed
- Fixed coding style issues

## [5.3.2] - 2017-06-11

### Fixed
- Fixed InlineAdd cancel button behavior to only trigger on left mouse button click and changed from mousedown to mouseup event (closes #445)
- Resolved issue where right-clicking on the InlineAdd cancel button would incorrectly trigger the cancel event

## [5.3.1] - 2017-06-11

### Fixed
- **Browser Compatibility**: Fixed missing `event.path` property in Firefox browsers by implementing a cross-browser compatible DOM path traversal function ([#497](https://github.com/contributte/datagrid/issues/497))
- **Template Layout**: Fixed submit button display in collapsible filter blocks when autosubmit is disabled and outer rendering is enabled ([#511](https://github.com/contributte/datagrid/issues/511))

### Technical Details

#### Browser Compatibility Fix
Prior to this release, the datagrid's shift-click selection functionality relied on the `event.path` property, which is not available in Firefox browsers. This caused JavaScript errors and prevented proper checkbox group selection when using Shift+Click.

**Before (not working in Firefox):**
```javascript
for (el in e.path) {
    if ($(el).is('.col-checkbox') && last_checkbox && e.shiftKey) {
        // Selection logic...
    }
}
```

**After (cross-browser compatible):**
```javascript
getEventDomPath = function(e) {
    if (path in e) {
        return e.path;
    }
    
    path = [];
    node = e.target;
    
    while (node !== document.body) {
        path.push(node);
        node = node.parentNode;
    }
    
    return path;
}

for (el in getEventDomPath(e)) {
    if ($(el).is('.col-checkbox') && last_checkbox && e.shiftKey) {
        // Selection logic...
    }
}
```

#### Template Layout Fix
When using filters with `autosubmit` set to `false` and `outerFilterRendering` set to `true`, the submit button was not properly displayed within the collapsible filter container.

## [5.3.0] - 2017-06-10

### New Features

- **Selected rows counter**: Added visual counter showing selected rows (e.g. "3/15") for group actions. Can be disabled via `setShowSelectedRowsCount(false)`. ([#5812557](https://github.com/ublaboo/datagrid/commit/5812557))
- **FilterSelect prompt management**: Added `setPrompt()` and `getPrompt()` methods to `FilterSelect` class, allowing dynamic control over select filter prompts. Prompt can now be reset using `setPrompt(null)`. ([#521](https://github.com/ublaboo/datagrid/pull/521))

### Improvements

- **Enhanced NetteDatabaseTableDataSource**: Added support for grouped columns and views without primary keys. The data source now properly handles `COUNT()` operations on grouped queries by using `DISTINCT` counting. ([#532](https://github.com/ublaboo/datagrid/pull/532))
- **Template improvements**: Fixed header column row structure to properly handle group actions when filters are present and outer filter rendering is disabled.

### Bug Fixes

- **ArrayDataSource count accuracy**: Fixed incorrect count reporting after data filtering. The data source now returns accurate counts that reflect the filtered dataset instead of the original dataset size. ([#490](https://github.com/ublaboo/datagrid/pull/490))
- **JavaScript compatibility**: Replaced deprecated jQuery `.size()` method with `.length` for better compatibility with newer jQuery versions.
- **Filter value display**: Fixed issue where filter values set from PHP were not properly displayed in HTML inputs. ([#46b0e71](https://github.com/ublaboo/datagrid/commit/46b0e71))
- **Date range filter**: Fixed undefined variable issues in date range filtering. ([#537](https://github.com/ublaboo/datagrid/pull/537))

### Documentation

- **Template comments**: Fixed typo in template documentation, replacing "walkaround" with "workaround".

### Internal Changes

- **Code style improvements**: Applied consistent formatting and coding standards across NetteDatabaseTableDataSource.
- **JavaScript enhancements**: Improved group action selection logic with better counter management and row selection feedback.

**Migration Notes:** This release is backward compatible. The selected rows counter is enabled by default but can be disabled if needed. All new FilterSelect prompt methods maintain existing behavior when not explicitly used.

## [5.2.4] - 2017-04-24

### Improvements

- **Enhanced extensibility of FunctionSum aggregation class**
  - Removed `final` keyword from `FunctionSum` class to allow inheritance and customization
  - Changed property visibility from `private` to `protected` for `$column`, `$result`, `$dataType`, and `$renderer` properties
  - This change enables developers to extend the sum aggregation functionality with custom implementations

### Technical Details

The `FunctionSum` aggregation class has been made extensible by removing the `final` modifier and changing private properties to protected visibility. This allows developers to create custom sum aggregation classes that inherit from `FunctionSum`.

**Before (v5.2.3):**
```php
final class FunctionSum implements IAggregationFunction
{
    private $column;
    private $result = 0;
    private $dataType;
    private $renderer;
    // ...
}
```

**After (v5.2.4):**
```php
class FunctionSum implements IAggregationFunction
{
    protected $column;
    protected $result = 0;
    protected $dataType;
    protected $renderer;
    // ...
}
```

This is a **non-breaking change** that maintains full backward compatibility while enabling new extensibility scenarios.

## [5.2.3] - 2017-04-23

### Fixed
- Fixed AJAX response handling in datagrid template by properly separating tbody block definition
- Resolved template structure issue that was causing AJAX updates to fail in certain scenarios
- **Technical**: Moved `n:block="tbody"` attribute to separate `{block tbody}` declaration in datagrid.latte template

### Details
The fix addresses an issue where the Latte template's tbody block was incorrectly structured, causing problems with AJAX snippet updates. The change separates the block definition from the HTML element attributes, ensuring proper template inheritance and AJAX functionality.

## [5.2.2] - 2017-04-20

### New Features

- **Aggregation Functions**: Added custom renderer support to `FunctionSum` aggregation function
  ```php
  $aggregationFunction->setRenderer(function ($result) {
      return number_format($result, 2) . ' CZK';
  });
  ```
- **Data Sources**: Added `IAggregatable` interface for improved aggregation support across different data source types
- **Doctrine Collection Support**: Enhanced `FunctionSum` to work with Doctrine Collections using `PropertyAccessHelper`

### Improvements

- **Performance**: Reduced unnecessary `getTemplate()` calls in DataGrid rendering by caching template instance
- **Template Optimization**: Unified columns summary and aggregation functions rendering into a single table row with snippet support
- **Code Quality**: 
  - Made `FunctionSum` class final and improved encapsulation by changing protected properties to private
  - Enhanced property access with new `PropertyAccessHelper::getValue()` method
  - Improved code formatting and consistency across aggregation-related files

### Bug Fixes

- **Aggregation**: Fixed missing return statement in Collection aggregation implementation
- **Property Reading**: Fixed property reading for `Doctrine\Collection` dataSource aggregation function  
- **DataModel Events**: Fixed DataModel events now properly called for single item filtering via `filterRow()` method
- **AJAX Updates**: Fixed summary section not updating during AJAX operations - added `redrawControl('summary')` calls

### Technical Changes

- **Interface Updates**: 
  - Removed mandatory `IDataSource` typehint from aggregation functions
  - Added proper typehints for `IAggregationFunction` interface
- **Data Source Enhancements**:
  - `DibiFluentDataSource`, `DoctrineDataSource`, and `DoctrineCollectionDataSource` now implement `IAggregatable`
  - Added proper aggregation function availability checks before execution
- **Template Structure**: Improved template organization by consolidating summary and aggregation rows

### Compatibility

- **Non-breaking**: All changes maintain backward compatibility
- **Enhanced Support**: Better support for different data source types in aggregation functions
- **Improved Error Handling**: Added validation to ensure data sources support aggregation before processing

This release focuses on enhancing the aggregation functionality and improving template rendering performance while maintaining full backward compatibility.

## [5.2.1] - 2017-04-19

### Fixed
- Fixed checkbox column visibility for group actions - column with checkboxes is now always shown when group actions are defined, even when no rows have actions available ([#516](https://github.com/contributte/datagrid/pull/516))
- Fixed pagination display when no records are present - first record number now correctly shows 0 instead of 1 when there are no items
- Fixed MultiAction functionality in tree view mode - actions now properly receive the `row` parameter in templates ([#518](https://github.com/contributte/datagrid/pull/518))
- Fixed Nextras data source sorting to properly handle column traversing in basic sortable usage by applying column preparation to sort parameters

### Improved
- Enhanced template performance by introducing `hasGroupActions` template variable to avoid repeated method calls
- Added `thead-group-action` AJAX snippet for better partial updates of group action header
- Added `tbody` block to table template for improved customization capabilities
- Optimized group action checkbox rendering logic in templates

### Technical Changes
- Template variable `hasGroupActionOnRows` usage refined for better logic separation
- Added proper AJAX redraw control for group action header (`thead-group-action`)
- Enhanced Nextras data source with column preparation in sorting logic

## [5.2.0] - 2017-04-10

### Added
- **Aggregation support for Doctrine data source**: Added support for sum aggregation functions when using Doctrine QueryBuilder as data source
- **Configurable data type for sum aggregation**: `FunctionSum` constructor now accepts an optional `$dataType` parameter to specify whether to use paginated, filtered, or all data for calculations

### Improved
- **Enhanced DoctrineDataSource performance**: Implemented conditional use of Doctrine Paginator based on query complexity
  - Uses simple COUNT queries for basic queries without joins or GROUP BY clauses
  - Falls back to Paginator only when necessary (queries with joins or GROUP BY)
  - Improves performance for simple queries by avoiding unnecessary Paginator overhead
- **Extended sum aggregation functionality**: `FunctionSum` now supports Doctrine QueryBuilder in addition to existing data sources
  - Automatically handles column aliasing for Doctrine queries
  - Preserves existing functionality for other data source types

### Changed
- **FunctionSum constructor signature**: Added optional `$dataType` parameter with default value `IAggregationFunction::DATA_TYPE_PAGINATED`
  ```php
  // Before
  public function __construct($column)
  
  // After  
  public function __construct($column, $dataType = IAggregationFunction::DATA_TYPE_PAGINATED)
  ```
- **DoctrineDataSource query optimization**: Modified `getCount()` and `getData()` methods to use Paginator only when query complexity requires it

### Technical Details
- Added `processAggregation()` method to DoctrineDataSource for handling aggregation callbacks
- Implemented `usePaginator()` private method to determine when Paginator is necessary
- Enhanced column alias detection in FunctionSum for Doctrine queries
- Improved data source cloning to prevent side effects during aggregation processing

### Backward Compatibility
- All changes are backward compatible
- Existing `FunctionSum` usage will continue to work without modification
- DoctrineDataSource performance improvements are transparent to existing implementations

This release focuses on performance improvements for Doctrine-based data sources and extends aggregation functionality to support more complex use cases while maintaining full backward compatibility.

## [5.1.2] - 2017-03-13

### Bug Fixes
- **Fixed per-page submit functionality**: Improved autosubmit behavior for pagination controls to support both `input[type=submit]` and `button[type=submit]` elements (#486)
- **Fixed template variable conflict**: Resolved "The variable 'rows' already exists" error by switching from `add()` method to direct property assignment in template rendering (#483)
- **Fixed pagination display**: Corrected the display of first record number from 0 to 1 in pagination info text (#493)

### Improvements
- **Enhanced Doctrine parameter handling**: Switched from positional parameters (`?`) to named parameters (`:param`) in DoctrineDataSource for better query readability and maintainability (#447)
  ```php
  // Before
  $this->data_source->andWhere("$c = ?$p")->setParameter($p, $value);
  
  // After
  $this->data_source->andWhere("$c = :$p")->setParameter($p, $value);
  ```
- **Improved DateTimeImmutable support**: Added native support for `DateTimeImmutable` objects in `DateTimeHelper::fromString()` method, allowing seamless conversion between `DateTimeImmutable` and `DateTime` instances (#488)

### New Features
- **DateTimeImmutable compatibility**: The datagrid now properly handles `DateTimeImmutable` objects in date filtering and processing, maintaining timezone information during conversion

### Technical Details
- **Frontend assets**: Updated compiled JavaScript assets to reflect the per-page submit improvements
- **Template optimization**: Streamlined template variable assignment for better performance and code clarity
- **Query optimization**: Enhanced Doctrine query building with more readable named parameter syntax

## [5.1.1] - 2017-03-01

### Bug Fixes

- **Fixed Dibi 2.x compatibility** ([#479](https://github.com/ublaboo/datagrid/pull/479), [#480](https://github.com/ublaboo/datagrid/pull/480))
  - Added backward compatibility for `Dibi\Helpers::escape()` method which was removed in Dibi 2.x
  - Fixed filter text escaping to work with both Dibi 2.x and 3.x versions
  - Updated `DibiFluentDataSource` and `DibiFluentMssqlDataSource` to handle API changes gracefully
  - Added support for legacy `DibiFluent` class alongside `Dibi\Fluent`

### Improvements

- **Removed unused dependency** `ublaboo/controls` from composer.json to reduce package footprint
- **Code style improvements** with consistent formatting in composer.json

### Technical Details

The main compatibility issue addressed the removal of `Dibi\Helpers::escape()` in Dibi 2.x. The fix implements a fallback mechanism:

```php
// Before (only worked with Dibi 3.x+)
$column = Dibi\Helpers::escape($driver, $column, \dibi::IDENTIFIER);

// After (works with both Dibi 2.x and 3.x+)
if (class_exists(Dibi\Helpers::class) === TRUE) {
    $column = Dibi\Helpers::escape($driver, $column, \dibi::IDENTIFIER);
} else {
    $column = $driver->escape($column, \dibi::IDENTIFIER);
}
```

This release ensures continued compatibility with projects using Dibi 2.x while maintaining support for newer versions.

## [5.1.0] - 2017-02-28

### Added
- Added explicit PHP version requirement (^5.6|^7.0) in composer.json
- Added support for \Dibi\Fluent annotation alongside existing \DibiFluent
- Added `onFiltersAssembled` event as replacement for deprecated `onFiltersAssabled`
- Added fluent interface support: `setMultiSortEnabled()`, `removeColumn()`, `removeAction()`, `removeFilter()`, `setStrictSessionFilterValues()`, and `removeToolbarButton()` now return `$this`
- Added `getToolbarButton()` method to retrieve existing toolbar buttons by key
- Added `removeToolbarButton()` method to remove toolbar buttons
- Added validation scope to inline edit and inline add submit buttons
- Added validation scope to group action submit buttons

### Changed
- **BREAKING**: Removed Nette 2.4 version constraints from composer dependencies (nette/application, nette/forms, nette/database)
- **BREAKING**: Toolbar buttons are now keyed by href parameter instead of being appended to array
- **BREAKING**: `addToolbarButton()` now throws `DataGridException` if button with same key already exists
- **BREAKING**: `getSortableParentPath()` now uses `Nette\Application\IPresenter` instead of `Nette\Application\UI\Control`
- Improved method return types and documentation
- Enhanced validation scoping for form submissions to prevent unwanted validation conflicts

### Deprecated
- Deprecated `$onFiltersAssabled` property in favor of `$onFiltersAssembled` (typo fix)
- Deprecated `assableFilters()` method in favor of `assembleFilters()` (typo fix)

### Fixed
- Fixed typo in method name: `assableFilters()` → `assembleFilters()`
- Fixed typo in property name: `$onFiltersAssabled` → `$onFiltersAssembled`
- Fixed typo in PHPDoc: "strign" → "string"
- Fixed whitespace issues and code formatting
- Fixed toolbar button management by using proper keyed array structure
- Fixed validation scope issues in inline editing and group actions

### Security
- Added Dibi 3.0 support with proper type annotations

## [5.0.8] - 2017-02-01

### New Features

- **Group Multi-Select Actions**: Added support for multi-select dropdown in group actions
  - New `GroupMultiSelectAction` class for creating multi-select group actions
  - Added `DataGrid::addGroupMultiSelectAction($title, $options)` method
  - Enhanced frontend JavaScript support with Bootstrap Selectpicker integration

- **Action Link Behavior**: Added ability to open action links in new tabs/windows
  - New `Action::setOpenInNewTab($open_in_new_tab = TRUE)` method
  - New `Action::isOpenInNewTab()` method to check current setting
  - Automatically adds `target="_blank"` attribute when enabled

### Improvements

- **Enhanced Database Driver Support**: Extended Dibi driver compatibility
  - Added support for `Dibi\Drivers\SqlsrvDriver` with MSSQL data source
  - Updated class references from deprecated `DibiOdbcDriver`/`DibiMsSqlDriver` to new namespaced versions
  - Improved consistency in driver detection logic

- **Inline Edit Method Chaining**: Enhanced fluent interface support
  - `InlineEdit::setItemId($id)` now returns `$this` for method chaining
  - `InlineEdit::setShowNonEditingColumns($show)` now returns `$this` for method chaining

### Bug Fixes

- **Inline Edit Error Handling**: Fixed frontend behavior when inline edit operations fail
  - Properly restores original cell value when edit request fails
  - Improved error state handling in CoffeeScript/JavaScript

- **Group Action Styling**: Enhanced multi-select group action rendering
  - Fixed CSS class application for Bootstrap Selectpicker
  - Improved icon handling with FontAwesome integration
  - Better attribute management for proper form control behavior

### Technical Details

**New Classes:**
- `Ublaboo\DataGrid\GroupAction\GroupMultiSelectAction` - Extends `GroupSelectAction` with multi-select functionality

**Enhanced Methods:**
```php
// New action methods
$action->setOpenInNewTab(true);  // Open link in new tab
$grid->addGroupMultiSelectAction('Bulk Status', $statusOptions);

// Enhanced method chaining
$inlineEdit->setItemId($id)->setShowNonEditingColumns(false);
```

**Database Compatibility:**
- Improved support for Microsoft SQL Server through various Dibi drivers
- Better compatibility with modern Dibi library versions

This release focuses on extending group action capabilities with multi-select functionality, improving action link behavior, and enhancing database driver compatibility while maintaining backward compatibility.

## [5.0.7] - 2017-01-27

### Fixed
- Fixed broken method chaining in Filter classes by adding missing `return $this` statements
  - `Filter::setValue()` now returns `static` for method chaining
  - `Filter::setCondition()` now returns `static` for method chaining  
  - `FilterDate::setFormat()` now returns `static` for method chaining
  - `FilterDateRange::setFormat()` now returns `static` for method chaining
- Fixed filter collapsing when multiple datagrids are used on the same page
  - Updated template to use unique IDs with datagrid name: `datagrid-{$control->getName()}-row-filters`
  - Prevents conflicts between multiple datagrids' filter collapse functionality

### Changed  
- Improved code formatting and documentation consistency in filter classes
  - Standardized parameter alignment in PHPDoc comments
  - Added missing `@return static` annotations for fluent interface methods

## [5.0.6] - 2017-01-15

### Added
- Added configurable attributes parameter to `setEditableInputTypeSelect()` method for enhanced customization

### Fixed
- Fixed inline edit display issue where select dropdowns would show the selected value instead of the display text after form submission

### Technical Details

#### Enhanced Inline Edit Select Configuration
The `setEditableInputTypeSelect()` method now accepts an optional `$attrs` parameter:

**Before:**
```php
public function setEditableInputTypeSelect(array $options = [])
```

**After:**
```php
public function setEditableInputTypeSelect(array $options = [], array $attrs = [])
```

This change allows developers to pass additional HTML attributes to select elements used in inline editing, providing greater customization flexibility.

#### Fixed Select Display Value
Previously, when using select dropdowns in inline editing mode, after successful form submission the cell would display the selected value instead of the human-readable display text. This has been corrected so that:

- For select elements: The display text (option content) is shown
- For other input types: The actual value continues to be displayed as before

**JavaScript Fix:**
```javascript
// Before
cell.html(value);

// After  
if (cell.data('datagrid-editable-type') === 'select') {
    cell.html(input.find('option[value=' + value + ']').html());
} else {
    cell.html(value);
}
```

This ensures a better user experience by showing meaningful text rather than internal values in the datagrid interface.

## [5.0.5] - 2017-01-07

### Added
- New `setTranslatableHeader()` and `isTranslatableHeader()` methods in Column class to control column header translation
- Element caching system in Column class for improved performance with `$elementCache` property
- `setAttribute()` method in Filter class for setting single attributes
- Support for disabling column header translation on individual columns (#435)

### Changed
- **BREAKING**: Refactored `getElementPrototype()` method in Column class - now returns cached element without parameters
- **BREAKING**: Method `getElementPrototype($tag, $key, $row)` renamed to `getElementForRender($tag, $key, $row)` 
- Rewrote filter attributes handling system - attributes now stored as associative array instead of indexed array (#434)
- Filter attribute handling now properly merges and deduplicates class values
- Improved element prototype caching to reduce HTML element creation overhead

### Fixed
- Fixed "Maximum call stack size exceeded" error when form is submitted via JavaScript (#437)
- Fixed variable scope issue after refactoring column methods into separate functions
- Fixed spinner triggering on filter reset operations
- Resolved filter attributes not being properly applied due to array structure changes

### Technical Details

**Column Element Prototype Changes:**
```php
// Before v5.0.5 - single method handled both cases
public function getElementPrototype($tag, $key = NULL, Row $row = NULL)

// After v5.0.5 - split into two methods
public function getElementPrototype($tag)  // Returns cached element
public function getElementForRender($tag, $key, Row $row = NULL)  // For template rendering
```

**Filter Attributes Structure:**
```php
// Before v5.0.5
protected $attributes = [
    ['class', 'form-control input-sm']
];

// After v5.0.5  
protected $attributes = [
    'class' => ['form-control', 'input-sm'],
];
```

**Column Translation Control:**
```php
// New functionality to disable translation on specific columns
$column->setTranslatableHeader(false);
```

This release focuses on performance improvements through element caching, better attribute handling in filters, and enhanced control over column header translation. The breaking changes primarily affect internal APIs and should have minimal impact on standard usage patterns.

## [5.0.4] - 2016-12-24

### Added
- Added Shift+click group selection functionality for checkbox columns - users can now select multiple rows by holding Shift and clicking checkboxes to select ranges of items
- Added support for custom "throughColumn" specification in ActiveRow relationships, allowing more flexible database relationship handling

### Improved  
- Refactored ActiveRow property access logic by extracting `getActiveRowProperty()` method from main `getValue()` method for better code organization and maintainability
- Enhanced relationship column syntax support with optional throughColumn parameter using format `:table.column:throughColumn` and `table.column:throughColumn`

### Fixed
- Fixed issue with calling referred columns in ActiveRow relationships (#436)
- Improved error handling for missing related records - now returns NULL instead of causing exceptions when referenced data doesn't exist

### Technical Details
**Breaking Changes**: None

**New Features**:
- **Shift+Click Selection**: Users can now select ranges of checkboxes by holding Shift key and clicking - automatically selects all checkboxes between the last clicked and current checkbox
- **Enhanced Column Syntax**: Added support for throughColumn in relationship columns:
  - Related tables: `:tableName.columnName:throughColumn` 
  - Referenced tables: `tableName.columnName:throughColumn`

**Code Examples**:
```php
// Before v5.0.4 - limited relationship syntax
$grid->addColumnText('related', ':users.name');

// After v5.0.4 - with throughColumn support
$grid->addColumnText('related', ':users.name:user_id');
$grid->addColumnText('referenced', 'categories.name:category_id');
```

**Migration Notes**: This release is fully backward compatible. Existing column syntax continues to work unchanged, while new throughColumn syntax is optional and provides additional flexibility for complex relationships.

The refactored code improves maintainability by separating ActiveRow-specific logic into its own method while maintaining the same public API.

## [5.0.3] - 2016-12-20

### Fixed
- Fixed MultiAction column icon spacing calculation by using correct name parameter in `tryAddIcon()` method
  - **Before**: `$this->tryAddIcon($button, $this->getIcon(), $this->getText())`
  - **After**: `$this->tryAddIcon($button, $this->getIcon(), $this->getName())`
  - This ensures proper spacing is calculated based on the actual column name length rather than the display text

**Technical Details:**
The `MultiAction::renderButton()` method was incorrectly passing `$this->getText()` to the `tryAddIcon()` helper method instead of `$this->getName()`. The `tryAddIcon()` method uses the third parameter to determine whether to add spacing (`&nbsp;`) after the icon based on the string length. This fix ensures the spacing calculation uses the column's actual name rather than its display text, which could be empty or have different length characteristics.

## [5.0.2] - 2016-12-20

### Added
- Added secondary icon support for status column options through `setIconSecondary()` and `getIconSecondary()` methods in `Option` class
- Added CSS styling for status option secondary icons with `.datagrid-column-status-option-icon` class

### Changed
- **Template Translation System**: Replaced deprecated `{_'key'}` translation syntax with modern `{='key'|translate}` filter syntax across all templates
  - Updated pagination template (`data_grid_paginator.latte`)
  - Updated main datagrid template (`datagrid.latte`) 
  - Updated tree view template (`datagrid_tree.latte`)
  - Updated status column template (`column_status.latte`)

### Fixed
- **Database Search Performance**: Removed forced collation handling in `DibiFluentDataSource` text search functionality
  - Simplified text search query generation by removing `utf8_bin` and `utf8_general_ci` collation forcing
  - Eliminated unnecessary ASCII conversion logic that could cause encoding issues
  - This change allows the database engine to handle text search more efficiently using default collation settings

### Development
- Added `.idea` directory to `.gitignore` for improved developer experience with PhpStorm/IntelliJ IDEs

**Technical Details:**

The major improvements in this release focus on template system modernization and database performance optimization. The transition from the deprecated `{_'key'}` syntax to the `{='key'|translate}` filter provides better compatibility with newer Latte template versions and improves translation handling consistency.

The database search fix addresses potential performance issues in the `DibiFluentDataSource` where forced collation handling was causing unnecessary complexity and potential encoding problems. By removing the collation forcing logic, search queries will now use the database's default collation settings, which is typically more efficient and appropriate for most use cases.

The addition of secondary icon support for status columns provides enhanced UI flexibility for displaying additional visual indicators alongside status options.

## [5.0.1] - 2016-12-09

### Bug Fixes

- **Filter Reset**: Fixed JavaScript functionality for resetting individual column filters. The filter reset now correctly uses the dynamic datagrid name instead of the hardcoded `examplesGrid` prefix, ensuring proper functionality across different datagrid instances.

### Improvements

- **Fluent Interface**: Enhanced `ColumnLink::setOpenInNewTab()` method to return `$this`, making it consistent with the fluent interface pattern used throughout the library.

### Documentation

- **Type Annotations**: Fixed incorrect DocBlock return type annotation in `DataGrid::createSorting()` method from `@return void` to `@return Sorting`.

### Maintenance

- **Repository Cleanup**: Removed `coverage.xml` file from version control and updated `.gitignore` to exclude coverage files and follow PHP best practices for dependency management.

**Technical Details:**

The main bug fix addressed an issue in the JavaScript assets where the filter reset functionality was using a hardcoded grid name (`examplesGrid`) instead of the dynamic grid name from the payload. This caused the reset filter by column feature to fail when the datagrid had a different name.

**Before:**
```javascript
new_href = href.replace('do=examplesGrid-resetFilter', 'do=' + payload._datagrid_name + '-resetColumnFilter')
```

**After:**
```javascript
new_href = href.replace('do=' + payload._datagrid_name + '-resetFilter', 'do=' + payload._datagrid_name + '-resetColumnFilter')
```

The `ColumnLink::setOpenInNewTab()` method was enhanced to maintain consistency with the library's fluent interface pattern:

```php
public function setOpenInNewTab($open_in_new_tab = TRUE)
{
    $this->open_in_new_tab = $open_in_new_tab;
    return $this;  // Added for fluent interface
}
```

## [5.0.0] - 2016-12-04

### BREAKING CHANGES

**PHP Version Requirements**
- **Dropped support for PHP 5.5** - Minimum PHP version is now 5.6+

**Trait Refactoring**
- **Removed `TButton` trait** - Replaced with multiple specialized traits:
  - `TButtonCaret` - For dropdown caret functionality  
  - `TButtonClass` - For CSS class management
  - `TButtonIcon` - For icon handling
  - `TButtonText` - For button text
  - `TButtonTitle` - For button title attributes
  - `TButtonTryAddIcon` - For conditional icon addition

**Method Signature Changes**
- **`setDefaultSort()` method signature changed** - Added optional `$use_on_reset` parameter:
  ```php
  // Before
  public function setDefaultSort($sort)
  
  // After  
  public function setDefaultSort($sort, $use_on_reset = TRUE)
  ```

**Exception Classes**
- **New specific exception classes** - More granular error handling:
  - `DataGridColumnNotFoundException` - Thrown instead of generic `DataGridException` for missing columns
  - `DataGridFilterNotFoundException` - Thrown instead of generic `DataGridException` for missing filters

**Method Name Corrections**
- **Fixed typo in method name** - `deleteSesssionData()` renamed to `deleteSessionData()`

### NEW FEATURES

**Aggregation Functions**
- **Added aggregation function system** - Support for data aggregation across columns:
  - New `IAggregationFunction` interface for custom aggregation logic
  - `IMultipleAggregationFunction` interface for complex aggregations  
  - `FunctionSum` implementation for sum calculations
  - `TDataGridAggregationFunction` trait added to main DataGrid class
  - Currently supports DibiFluentDataSource with more data sources planned

**MultiAction Column**
- **Added MultiAction dropdown column** - Groups multiple actions in a Bootstrap dropdown:
  ```php
  $grid->addMultiAction('actions', 'Actions')
      ->addAction('edit', 'Edit', 'edit')
      ->addAction('delete', 'Delete', 'delete');
  ```
  - Support for conditional row actions via `setRowCondition()`
  - Integrated with existing action system

**Multi-Sort Capability**
- **Added multi-column sorting** - Users can now sort by multiple columns simultaneously
- New `$multiSort` property and related functionality
- Session tracking for sorting state with `_grid_has_sorted`

**Related Column Names Support**
- **Basic support for related column names** - Enables sorting and filtering on related model properties

**Enhanced Filter Features**  
- **Collapsible outer filters** - Filters rendered outside the grid can now be collapsed/expanded
- **Individual filter reset** - Reset specific filters instead of all filters at once
- **Range filter placeholders** - Added placeholder support for range filter inputs
- **Filter date interface** - New `IFilterDate` interface for date filter implementations

**Row Conditions for Item Detail**
- **Conditional item detail display** - Show/hide detailed row information based on custom logic

### IMPROVEMENTS

**Session Management**
- **Enhanced session handling** - Better management of grid state persistence
- **New session keys** - Added `_grid_has_sorted` for sorting state tracking
- **Improved session data cleanup** - More reliable session data management

**Form Handling**
- **Smarter form defaults** - Filter form defaults only set when form is not yet submitted
- **Better error handling** - Improved validation and error reporting for inline editing

**UI/UX Enhancements**
- **Error highlighting for inline edit** - Visual feedback when inline editing fails
- **Icon consistency** - Unified icon tag usage to `<i>` elements
- **Improved Bootstrap integration** - Better dropdown and component styling

**Code Quality**
- **PHPDoc improvements** - Better type hints, changed `boolean` to `bool`
- **Added Scrutinizer configuration** - Code coverage and quality analysis setup
- **Enhanced Travis CI configuration** - Improved continuous integration setup

**JavaScript/CSS**
- **Updated assets** - Refreshed compiled CSS and JavaScript files
- **Improved CoffeeScript** - Enhanced client-side functionality
- **SCSS improvements** - Better styling and responsive design

### BUG FIXES

- **Fixed double confirmation before AJAX call** - Eliminated duplicate confirmation dialogs
- **Fixed date sorting issues** - Proper date comparison and ordering  
- **Fixed session issues when columns get renamed** - Better handling of column identifier changes
- **Fixed Dibi column name escaping** - Proper SQL identifier escaping for security
- **Fixed MSSQL data source compatibility** - Better support for SQL Server databases
- **Fixed HTML table syntax** - Corrected template markup issues

### DEPENDENCIES

- **Added Dibi 3.0 support** - `dibi/dibi: ^3.0` added to optional dependencies

### UPGRADE GUIDE

**For users extending button functionality:**
Replace usage of the removed `TButton` trait with specific button traits:

```php
// Before
use Ublaboo\DataGrid\Traits\TButton;

class CustomAction {
    use TButton;
}

// After  
use Contributte\Datagrid\Traits\TButtonIcon;
use Contributte\Datagrid\Traits\TButtonClass;
use Contributte\Datagrid\Traits\TButtonText;
use Contributte\Datagrid\Traits\TButtonTitle;

class CustomAction {
    use TButtonIcon, TButtonClass, TButtonText, TButtonTitle;
}
```

**For custom exception handling:**
Update catch blocks to use specific exception types:

```php  
// Before
try {
    $column = $grid->getColumn($key);
} catch (DataGridException $e) {
    // handle error
}

// After
try {
    $column = $grid->getColumn($key);
} catch (DataGridColumnNotFoundException $e) {
    // handle missing column
}
```

**For PHP 5.5 users:**
Upgrade to PHP 5.6 or higher before updating to this version.

---

This release represents a significant evolution of the DataGrid component with enhanced functionality for complex data operations, improved user experience, and better code organization. The breaking changes are primarily related to internal API improvements and should have minimal impact on typical usage patterns.

---

*For detailed upgrade instructions between major versions, see [UPGRADE.md](UPGRADE.md).*