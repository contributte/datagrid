# Documentation Review - TODO

This document lists typos, grammar issues, and missing/incorrect sections found in the documentation.

## Typos (FIXED)

The following typos have been fixed:

### introduction.md
- [x] `PostreSQL` -> `PostgreSQL`
- [x] `it's documentation` -> `its documentation`
- [x] Added missing `return $grid;` statement and return type

### columns.md
- [x] `it's own template` -> `their own template`
- [x] `a>` -> `<a>` (HTML tag)
- [x] `th>` and `td>` -> `<th>` and `<td>`
- [x] `from it's column` -> `from its column`
- [x] `id == 1` -> `id == 2` (corrected comment)

### filters.md
- [x] `FiterText` -> `FilterText`
- [x] Duplicate key `2` -> `3` in array
- [x] `[Introduction](index.md)` -> `[Assets](assets.md)`
- [x] `User registerd` -> `User registered` (both occurrences)
- [x] `fitler` -> `filter`
- [x] Added missing array brackets in `setDefaultFilter()`
- [x] `class =>;` -> `class =>`

### actions.md
- [x] `Smazat` -> `Delete`
- [x] `$satatus` -> `$status`
- [x] Added missing closing brace `}`

### data-source.md
- [x] `it's documentation` -> `its documentation`
- [x] `each of it's methods` -> `each of its methods`

### row.md
- [x] `Now all rows` -> `Not all rows`
- [x] `canDispleyProfile` -> `canDisplayProfile`
- [x] `canDispleySettings` -> `canDisplaySettings`

### inline-edit.md
- [x] `then is edited` -> `than is edited`
- [x] `useage` -> `usage`
- [x] `Bud if` -> `But if`

### group-action.md
- [x] Added missing closing parenthesis in sprintf call

### assets.md
- [x] `There are prepare` -> `There are prepared`
- [x] Fixed list numbering (2, 1 -> 1, 2)

### template.md
- [x] `table-hovertable-condensed` -> `table-hover table-condensed`

### localization.md
- [x] `czech` -> `Czech`
- [x] Added missing `return $grid;` statement

### export.md
- [x] `i don't like` -> `I don't like`
- [x] Added missing `of` in sentence

## Still Pending - Grammar and Style Issues

### Informal Tone (consider revising for consistency)
- **columns.md line 78**: "But hey, what if I want to replace..."
- **columns.md line 239**: "Well, not id, more likely `$primary_key`..."
- **data-source.md line 55**: "The idea is simply to forward..." and "Feel free to leave me a comment..."

### Inconsistent Terminology
- `datasource` vs `data source` - used inconsistently throughout docs
- `datagrid` vs `Datagrid` vs `DataGrid` - should be consistent

## Still Pending - Missing Documentation Sections

### Completely Missing Topics
1. **Aggregation Functions**: Only `FunctionSum` is documented, but other aggregation functions and how to create custom ones needs more detail
2. **Exception Handling**: No documentation on what exceptions the datagrid can throw and how to handle them
3. **Performance Optimization**: No section on optimizing large datasets
4. **Accessibility**: No documentation on accessibility features (ARIA labels, keyboard navigation)
5. **Testing**: No documentation on how to test datagrid components

### Incomplete Sections

#### MultiAction (actions.md)
- Missing documentation on:
  - How to set confirmation on nested actions
  - How to conditionally show/hide actions in MultiAction
  - Full list of available methods

#### Toolbar Button (actions.md)
- Only basic usage shown
- Missing:
  - Available classes and icons
  - How to add confirmation
  - How to make it ajax-enabled

#### Group Actions (group-action.md)
- Missing documentation for:
  - `addGroupTextareaAction()` - only mentioned briefly with no parameters explanation
  - `setAutoSelect()` method
  - How to disable group action submit button until selection

#### Columns Summary (columns.md)
- Missing:
  - `ColumnsSummary::setPositionTop()` method
  - How to style the summary row

#### Plugins (assets.md)
- `EditablePlugin` vs `InlinePlugin` - both mentioned but difference not explained
- Missing documentation on:
  - Creating custom plugins
  - Plugin lifecycle and events
  - Plugin configuration options

#### Filters (filters.md)
- Section "Filter type blocks" (line 137-140) is empty - just says "Macro" with no content

#### State Storage (state-storage.md)
- Missing:
  - Full list of what state is stored (filters, sorting, pagination, column visibility, etc.)
  - How to migrate from old session storage to new custom storage
  - Thread-safety considerations for custom implementations

## Still Pending - Code Alignment Issues

### Outdated/Incorrect References
1. **filters.md line 135**: References `vendor/ublaboo/datagrid/src/templates/datagrid_filter_text.latte` - verify path is correct

### API Changes Not Reflected
- Verify all documented method signatures match actual code
- Check if any deprecated methods are still documented

## Recommendations

### High Priority (DONE)
1. ~~Fix all typos (especially `PostreSQL`, `registerd`, `FiterText`)~~
2. ~~Add missing return statements in example code~~
3. ~~Fix the broken link to `index.md`~~

### Medium Priority
1. Complete the empty "Filter type blocks" section
2. Standardize terminology (datasource vs data source)
3. Add missing documentation for `addGroupTextareaAction()`
4. Document the difference between `EditablePlugin` and `InlinePlugin`

### Low Priority
1. Consider adjusting informal tone for consistency
2. Add more comprehensive examples for complex features
3. Add performance tips section
4. Add troubleshooting/FAQ section
