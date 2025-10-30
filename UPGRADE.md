# Upgrade Guide

This document provides detailed instructions for upgrading between major versions of Contributte Datagrid.

## Table of Contents

- [Upgrading from 7.0.x to 7.1.x](#upgrading-from-70x-to-71x)
- [Upgrading from 6.x to 7.0.x](#upgrading-from-6x-to-70x)
- [Upgrading from 5.x to 6.0.x](#upgrading-from-5x-to-60x)
- [Upgrading from 4.x to 5.0.x](#upgrading-from-4x-to-50x)

---

## Upgrading from 7.0.x to 7.1.x

### Overview
Version 7.1.x introduces a pluggable state storage system and removes several backward compatibility features that were deprecated in 7.0.x.

### Breaking Changes

#### 1. Translation Keys Migration Removed
**Impact:** High - affects applications using custom translations

**Before (v7.0.x):**
```php
// These were automatically converted to contributte_* keys
new SimpleTranslator([
    'ublaboo_datagrid.no_items' => 'No records found',
    'ublaboo_datagrid.choose' => 'Choose...',
    'ublaboo_datagrid.reset_filter' => 'Reset filter'
]);
```

**After (v7.1.x):**
```php
// Must use contributte_* keys directly
new SimpleTranslator([
    'contributte_datagrid.no_items' => 'No records found',
    'contributte_datagrid.choose' => 'Choose...',
    'contributte_datagrid.reset_filter' => 'Reset filter'
]);
```

**Migration Steps:**
1. Update all `ublaboo_datagrid.*` translation keys to `contributte_datagrid.*`
2. Search your codebase for `ublaboo_datagrid` and replace with `contributte_datagrid`

#### 2. HappyInputs Integration Removed
**Impact:** Medium - affects applications using HappyInputs

**Before (v7.0.x):**
```php
$grid->useHappyComponents(true);
$shouldUse = $grid->shouldUseHappyComponents();
```

**After (v7.1.x):**
```php
// These methods no longer exist
// Form inputs now use standard Bootstrap 5 classes automatically
```

**Migration Steps:**
1. Remove all calls to `useHappyComponents()` and `shouldUseHappyComponents()`
2. Update your CSS if you were relying on HappyInputs-specific styling
3. Form inputs will automatically use Bootstrap 5 classes

#### 3. Session Methods Renamed
**Impact:** High - affects applications using custom state management

**Before (v7.0.x):**
```php
$grid->setStrictSessionFilterValues(true);
$data = $grid->getSessionData($key);
$grid->saveSessionData($key, $value);
$grid->deleteSessionData($key);
```

**After (v7.1.x):**
```php
$grid->setStrictStorageFilterValues(true);
$data = $grid->getStorageData($key, $defaultValue);
$grid->saveStorageData($key, $value);
$grid->deleteStorageData($key);
```

**Migration Steps:**
1. Replace `setStrictSessionFilterValues` with `setStrictStorageFilterValues`
2. Replace `getSessionData` with `getStorageData` (note: new optional default value parameter)
3. Replace `saveSessionData` with `saveStorageData`
4. Replace `deleteSessionData` with `deleteStorageData`

#### 4. Legacy Assets Removed
**Impact:** Low to Medium - affects applications using deprecated assets

**Before (v7.0.x):**
```typescript
import 'datagrid-all';
import 'datagrid-all.min';
```

**After (v7.1.x):**
```typescript
import 'datagrid-full';
import 'datagrid-full.min';
```

**Migration Steps:**
1. Update asset imports from `datagrid-all.*` to `datagrid-full.*`
2. Update any build scripts that reference the old asset names

#### 5. Class Aliases Removed
**Impact:** Low - affects applications using ublaboo/* namespaces

**Before (v7.0.x):**
```php
// compatibility.php provided these aliases
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Column\ColumnText;
```

**After (v7.1.x):**
```php
// Must use Contributte namespaces directly
use Contributte\Datagrid\DataGrid;
use Contributte\Datagrid\Column\ColumnText;
```

### New Features

#### Pluggable State Storage System
**Benefit:** Allows custom storage backends for grid state

```php
use Contributte\Datagrid\Storage\IStateStorage;
use Contributte\Datagrid\Storage\SessionStateStorage;
use Contributte\Datagrid\Storage\NoopStateStorage;

// Default: Session-based storage (same as before)
$grid->setStateStorage(new SessionStateStorage());

// Alternative: No persistence
$grid->setStateStorage(new NoopStateStorage());

// Custom implementation
class RedisStateStorage implements IStateStorage {
    public function saveState(string $key, mixed $value): void {
        // Your Redis implementation
    }
    
    public function loadState(string $key, mixed $defaultValue = null): mixed {
        // Your Redis implementation
    }
    
    public function deleteState(string $key): void {
        // Your Redis implementation
    }
}

$grid->setStateStorage(new RedisStateStorage());
```

#### Doctrine Paginator Control
```php
// Disable Doctrine Paginator for performance optimization
$grid->getDataSource()->setUsePaginator(false);
```

### Deprecations

- `setStrictSessionFilterValues()` → Use `setStrictStorageFilterValues()`

---

## Upgrading from 6.x to 7.0.x

### Overview
Version 7.0.x is a major modernization release that requires PHP 8.2+ and removes legacy compatibility layers.

### System Requirements

#### PHP Version
**Before (v6.x):** PHP 7.2+
**After (v7.0.x):** PHP 8.2+

**Migration Steps:**
1. Upgrade your PHP version to 8.2 or higher
2. Update your server/container configurations
3. Test all functionality with PHP 8.2+

#### Dependencies
Update your `composer.json`:

```json
{
    "require": {
        "php": "^8.2",
        "contributte/datagrid": "^7.0"
    }
}
```

### Breaking Changes

#### 1. Legacy Compatibility Removal
All backward compatibility layers for the Ublaboo → Contributte transition have been removed.

**Migration Steps:**
1. Ensure all code uses `Contributte\Datagrid` namespace (should be done in 6.x upgrade)
2. Remove any references to `ublaboo/datagrid` package
3. Update translation keys to use `contributte_datagrid.*` prefix

#### 2. Modern PHP 8.2 Features
The codebase now utilizes PHP 8.2+ language features.

**Potential Impact:**
- Union types and intersection types usage
- Enum usage where applicable
- Improved type declarations
- Performance optimizations

### Preparation Steps

1. **Before upgrading to 7.0.x, ensure your 6.x installation is up to date:**
   ```bash
   composer require contributte/datagrid:^6.10
   ```

2. **Test thoroughly with the latest 6.x version**

3. **Update PHP and dependencies:**
   ```bash
   # Update PHP to 8.2+
   composer require php:^8.2
   
   # Update to 7.0.x
   composer require contributte/datagrid:^7.0
   ```

---

## Upgrading from 5.x to 6.0.x

### Overview
Version 6.0.x represents a major modernization with namespace changes, PHP 7.2+ requirement, and Nette 3.0 support.

### System Requirements

#### PHP Version
**Before (v5.x):** PHP 5.6+
**After (v6.0.x):** PHP 7.2+

#### Framework Support
**Before (v5.x):** Nette 2.4+
**After (v6.0.x):** Nette 3.0+

### Breaking Changes

#### 1. Namespace Migration
**Impact:** Critical - affects all imports

**Before (v5.x):**
```php
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Column\ColumnText;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataSource\DibiFluentDataSource;
```

**After (v6.0.x):**
```php
use Contributte\Datagrid\DataGrid;
use Contributte\Datagrid\Column\ColumnText;
use Contributte\Datagrid\Column\Action\Confirmation\StringConfirmation;
use Contributte\Datagrid\DataSource\DibiFluentDataSource;
```

**Migration Steps:**
1. **Update Composer dependency:**
   ```json
   {
       "require": {
           "contributte/datagrid": "^6.0"
       }
   }
   ```
   Remove `ublaboo/datagrid` from dependencies.

2. **Update all namespace imports:**
   - Search and replace `Ublaboo\\DataGrid` with `Contributte\\Datagrid`
   - Update all `use` statements throughout your codebase

3. **Update service configurations:**
   ```yaml
   # Before (Neon)
   services:
       - Ublaboo\DataGrid\DataGrid
   
   # After (Neon)  
   services:
       - Contributte\Datagrid\DataGrid
   ```

#### 2. Asset Structure Changes
**Impact:** Medium - affects frontend builds

**Before (v5.x):**
```html
<!-- Bootstrap 3 support -->
<link rel="stylesheet" href="datagrid.css">
<script src="datagrid.js"></script>
```

**After (v6.0.x):**
```html  
<!-- Bootstrap 4+ required -->
<link rel="stylesheet" href="datagrid.css">
<script src="datagrid.js"></script>
```

**Migration Steps:**
1. **Update to Bootstrap 4+:**
   - Remove Bootstrap 3 stylesheets
   - Install Bootstrap 4 or 5
   - Update your application's Bootstrap classes

2. **Review custom CSS:**
   - Check for conflicts with new CSS class names
   - Update any custom datagrid styling
   - Test responsive behavior

#### 3. Template System Updates
**Impact:** Medium - affects custom templates

**Migration Steps:**
1. Review any custom datagrid templates
2. Update template syntax for Latte 2.8+
3. Test all template rendering

#### 4. Method Signature Changes
**Impact:** Low to Medium - affects advanced usage

Some method signatures have been updated for better type safety:

**Migration Steps:**
1. Review any classes extending datagrid components
2. Update method signatures to match new type declarations
3. Test all custom extensions

### New Features in 6.0.x

#### Modern Asset Pipeline
- Webpack-based build system
- Improved CSS and JavaScript structure
- Better browser compatibility

#### Enhanced Type Safety
- Full PHP 7.2+ type declarations
- Improved IDE support and IntelliSense
- Better error reporting

#### Performance Improvements
- Optimized for large datasets
- Reduced memory usage
- Faster rendering

### Migration Checklist

- [ ] Update PHP to 7.2+
- [ ] Update Nette Framework to 3.0+  
- [ ] Update Bootstrap to 4+
- [ ] Change composer dependency from `ublaboo/datagrid` to `contributte/datagrid`
- [ ] Update all namespace imports from `Ublaboo\\DataGrid` to `Contributte\\Datagrid`
- [ ] Update service configurations (DI containers)
- [ ] Review and update custom CSS
- [ ] Test all datagrid functionality
- [ ] Update any custom templates
- [ ] Test with your specific data sources
- [ ] Review any custom extensions or traits

---

## Upgrading from 4.x to 5.0.x

### Overview
Version 5.0.x introduced significant architectural improvements including aggregation functions, multi-action columns, and enhanced filtering capabilities.

### System Requirements

#### PHP Version
**Before (v4.x):** PHP 5.5+
**After (v5.0.x):** PHP 5.6+

### Breaking Changes

#### 1. Trait Refactoring
**Impact:** High - affects custom button implementations

**Before (v4.x):**
```php
use Ublaboo\DataGrid\Traits\TButton;

class CustomAction {
    use TButton;
}
```

**After (v5.0.x):**
```php
use Ublaboo\DataGrid\Traits\TButtonIcon;
use Ublaboo\DataGrid\Traits\TButtonClass;
use Ublaboo\DataGrid\Traits\TButtonText;
use Ublaboo\DataGrid\Traits\TButtonTitle;

class CustomAction {
    use TButtonIcon, TButtonClass, TButtonText, TButtonTitle;
}
```

#### 2. Method Signature Changes
**Impact:** Medium - affects sorting configuration

**Before (v4.x):**
```php
public function setDefaultSort($sort)
```

**After (v5.0.x):**
```php
public function setDefaultSort($sort, $use_on_reset = true)
```

#### 3. Exception Handling
**Impact:** Medium - affects error handling

**Before (v4.x):**
```php
try {
    $column = $grid->getColumn($key);
} catch (DataGridException $e) {
    // handle error
}
```

**After (v5.0.x):**
```php
try {
    $column = $grid->getColumn($key);
} catch (DataGridColumnNotFoundException $e) {
    // handle missing column
} catch (DataGridFilterNotFoundException $e) {
    // handle missing filter
}
```

#### 4. Method Name Corrections
**Impact:** Low - affects session management

**Before (v4.x):**
```php
$grid->deleteSesssionData($key); // Note the typo
```

**After (v5.0.x):**
```php
$grid->deleteSessionData($key); // Fixed typo
```

### New Features in 5.0.x

#### Aggregation Functions
```php
use Ublaboo\DataGrid\AggregationFunction\FunctionSum;

$grid->addAggregationFunction('total', new FunctionSum('price'));
```

#### MultiAction Columns
```php
$grid->addMultiAction('actions', 'Actions')
    ->addAction('edit', 'Edit', 'edit')
    ->addAction('delete', 'Delete', 'delete');
```

#### Enhanced Filtering
```php
// Individual filter reset
$grid->addFilterText('name', 'Name')
    ->setCondition('ILIKE ?', '%?%');

// Collapsible outer filters
$grid->setOuterFilterRendering(true);
```

### Migration Steps

1. **Update PHP version to 5.6+**

2. **Update trait usage:**
   - Replace `TButton` trait with specific button traits
   - Update any custom action classes

3. **Update exception handling:**
   - Catch specific exception types instead of generic `DataGridException`

4. **Fix method signatures:**
   - Update `setDefaultSort()` calls if you were extending the class

5. **Fix typos:**
   - Replace `deleteSesssionData()` with `deleteSessionData()`

6. **Test thoroughly:**
   - Verify all existing functionality works
   - Test new features if desired

---

## General Migration Tips

### 1. Gradual Migration Approach
For large applications, consider a gradual migration:

1. **Update one major version at a time**
2. **Test thoroughly at each step**
3. **Use feature flags to isolate changes**
4. **Maintain backward compatibility during transition**

### 2. Testing Strategy
- **Unit tests:** Update mocks and test data
- **Integration tests:** Test with real data sources  
- **UI tests:** Verify visual appearance and behavior
- **Performance tests:** Ensure no regressions

### 3. Common Issues and Solutions

#### Namespace Issues
```bash
# Find all files using old namespace
grep -r "Ublaboo\\DataGrid" src/

# Use IDE refactoring tools for bulk updates
```

#### Asset Loading Issues
```html
<!-- Verify correct asset paths -->
<link rel="stylesheet" href="/path/to/datagrid.css">
<script src="/path/to/datagrid.js"></script>
```

#### Translation Issues
```php
// Verify translation keys are updated
$translator = new Nette\Localization\Translator([
    'contributte_datagrid.no_items' => 'No items found'
]);
```

### 4. Getting Help

If you encounter issues during migration:

1. **Check the changelog** for specific breaking changes
2. **Review GitHub issues** for similar problems
3. **Create minimal reproduction cases** for complex issues
4. **Consider professional support** for large migrations

---

## Version Support Matrix

| Version | PHP Support | Nette Support | Status |
|---------|-------------|---------------|--------|
| 7.1.x   | 8.2+        | 3.0+          | Active |
| 7.0.x   | 8.2+        | 3.0+          | Maintenance |
| 6.10.x  | 7.2-8.3     | 3.0+          | Security fixes only |
| 6.x     | 7.2-8.1     | 3.0+          | End of life |
| 5.x     | 5.6-7.4     | 2.4-3.0       | End of life |
| 4.x     | 5.5-7.1     | 2.3-2.4       | End of life |

---

*For more detailed information about specific changes, see the [CHANGELOG.md](CHANGELOG.md) file.*