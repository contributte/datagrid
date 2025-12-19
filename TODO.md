# Documentation Review - TODO

This document lists typos, grammar issues, and missing/incorrect sections found in the documentation.

## Typos

### introduction.md
- **Line 28**: `PostreSQL` should be `PostgreSQL`
- **Line 77**: Example factory is missing `return $grid;` statement at the end

### columns.md
- **Line 57**: `it's own template` should be `its own template` (possessive, not contraction)
- **Line 230**: `a>` should be `<a>` (HTML tag rendering issue)
- **Line 480**: `th>` and `td>` should be `<th>` and `<td>` (missing opening brackets)
- **Line 492**: Same as above - `th>` and `td>` should be `<th>` and `<td>`
- **Line 533**: Comment says `id == 1` but the condition in code is `id == 2` - inconsistent

### filters.md
- **Line 42**: `FiterText` should be `FilterText`
- **Line 186**: Duplicate key `2` in array: `[1 => 'On', 2 => 'Off', 2 => 'Another option']` - third should be `3`
- **Line 189**: References `[Introduction](index.md)` but file should be `introduction.md` or `README.md`
- **Line 194**: `User registerd` should be `User registered`
- **Line 204**: `User registerd` should be `User registered` (same typo again)
- **Line 248**: `fitler` should be `filter`
- **Line 251**: Missing array brackets - should be `$grid->setDefaultFilter(['id' => 10], false);`
- **Line 110**: Extra semicolon in Latte: `class =>;` should be `class =>`

### actions.md
- **Line 78**: `Smazat` is Czech - should use English `Delete` in English docs
- **Line 153**: Variable typo `$satatus` should be `$status`
- **Line 166**: Missing closing brace `}` for the function

### data-source.md
- **Line 17**: `it's documentation` should be `its documentation` (possessive)

### row.md
- **Line 16**: Awkward phrasing `Now all rows have to provide group action` - should be `Not all rows have to provide group action`
- **Line 59**: `canDispleyProfile` should be `canDisplayProfile`
- **Line 66**: `canDispleySettings` should be `canDisplaySettings`

### inline-edit.md
- **Line 57**: Section title `Render different content then is edited` - `then` should be `than`
- **Line 82**: `useage` should be `usage`

### group-action.md
- **Line 109**: Missing closing parenthesis in sprintf call

### assets.md
- **Line 99**: `There are prepare JS/TS` should be `There are prepared JS/TS`
- **Line 103-104**: List numbering is reversed (shows 2 then 1, should be 1 then 2)

### template.md
- **Line 72**: Missing space: `table-hovertable-condensed` should be `table-hover table-condensed`

### localization.md
- **Line 7**: `czech` should be capitalized as `Czech`

## Grammar and Style Issues

### Informal Tone (consider revising for consistency)
- **columns.md line 78**: "But hey, what if I want to replace..."
- **columns.md line 239**: "Well, not id, more likely `$primary_key`..."
- **data-source.md line 55**: "The idea is simply to forward..." and "Feel free to leave me a comment..."
- **export.md line 40**: "i don't like the idea" should be "I don't like the idea"

### Inconsistent Terminology
- `datasource` vs `data source` - used inconsistently throughout docs
- `datagrid` vs `Datagrid` vs `DataGrid` - should be consistent

### Missing Return Types in Examples
- **introduction.md line 71-78**: Factory method should have return type and return statement:
  ```php
  public function createComponentSimpleGrid($name): Datagrid
  {
      $grid = new Datagrid($this, $name);
      // ...
      return $grid;
  }
  ```
- **localization.md line 10-47**: Same issue - method declares return type but doesn't return

## Missing Documentation Sections

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

## Code Alignment Issues

### Outdated/Incorrect References
1. **filters.md line 135**: References `vendor/ublaboo/datagrid/src/templates/datagrid_filter_text.latte` - verify path is correct
2. **filters.md line 189**: Link to `index.md` doesn't exist

### API Changes Not Reflected
- Verify all documented method signatures match actual code
- Check if any deprecated methods are still documented

## Recommendations

### High Priority
1. Fix all typos (especially `PostreSQL`, `registerd`, `FiterText`)
2. Add missing return statements in example code
3. Fix the broken link to `index.md`
4. Complete the empty "Filter type blocks" section

### Medium Priority
1. Standardize terminology (datasource vs data source)
2. Add missing documentation for `addGroupTextareaAction()`
3. Document the difference between `EditablePlugin` and `InlinePlugin`
4. Add return types to all example factory methods

### Low Priority
1. Consider adjusting informal tone for consistency
2. Add more comprehensive examples for complex features
3. Add performance tips section
4. Add troubleshooting/FAQ section
