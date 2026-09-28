# Datagrid Design

Datagrid renders a data table with filters, sorting, pagination, inline editing, group actions and a tree view inside
a Nette application page. Developers embed it in their presenters; their users see and operate it.

## Principles

- **Markup is Bootstrap 5.** Templates use Bootstrap 5.3 classes and `data-bs-*` attributes. Host apps load their own
  Bootstrap build; `datagrid.css` only adds layout rules on top of it.
- **Server renders, TypeScript enhances.** Sorting, paging, filters and column actions are Nette signals rendered as
  links and forms with the `ajax` class. `naja` swaps the snippets; the plugins in `assets/plugins/` add behaviour.
- **Templates are an API.** Apps extend `datagrid.latte` with `{extends $originalTemplate}` and override its blocks.
- **Icons come from one prefix.** Every icon is `{$iconPrefix}name`, and `Datagrid::$iconPrefix` defaults to
  `fas fa-` (Font Awesome 6 Free). Most icons also sit in an `icon-*` block that apps can override.
- **Every label is translatable.** Texts use `contributte_datagrid.*` keys with English defaults in
  `src/Localization/SimpleTranslator.php`.

## Inventory

- Grid table, outer filters, toolbar, column settings, group actions, inline edit and add rows, summary row, footer
  with item count and per-page select: `src/templates/datagrid.latte`
- Tree view (extends the grid template, replaces the `data` block): `src/templates/datagrid_tree.latte`
- Filters: `src/templates/datagrid_filter_text.latte`, `_select`, `_date`, `_daterange`, `_range`
- Status dropdown and multi-action dropdown: `src/templates/column_status.latte`, `column_multi_action.latte`
- Pagination: `src/Components/DatagridPaginator/templates/data_grid_paginator.latte`
- Client behaviour: `assets/plugins/features/` (autosubmit, checkboxes, confirm, editable, inline, item detail, tree
  view) and `assets/plugins/integrations/` (datepicker, Nette Forms, selectpicker, sortable)
- Third-party widgets: `assets/integrations/` wraps `tom-select`, `vanillajs-datepicker` and `sortablejs`

## Layout

- The grid is a `div[data-datagrid-name]` with `padding: 1em` that fills its container; the host page sets the width.
- Group actions, exports, toolbar buttons and the column settings dropdown share the first `thead` row. Inline
  filters form the next header row; outer filters render above the table in `col-sm-{12 / columns}` cells.
- The footer row is split 25 / 50 / 25 %: item count, pagination, reset filter and per-page select.
- Action columns (`.col-action`) and checkbox columns (`.col-checkbox`, `2.1em`) shrink to their content.
- Spacing uses Bootstrap utilities plus the rules in `assets/css/datagrid.css`.

## Typography

- The grid sets no `font-family` and inherits the host page font.
- `datagrid.css` fixes a few sizes: `90%` for header cells, `12px` for dropdown items and `.btn-xs`, `14px` for the
  footer. The `.btn-xs` class exists only inside the grid; Bootstrap 5 has no such size.

## Colors and Tokens

- Source of truth: `assets/css/datagrid.css`, imported by `assets/css/datagrid-full.css` together with the
  `tom-select` and `vanillajs-datepicker` Bootstrap 5 themes and `assets/css/tom-select.css`.
- Secondary buttons use `Datagrid::$btnSecondaryClass` (default `btn-default btn-secondary`); templates never
  hard-code the class.
- The table class is the `table-class` block, by default `table table-hover table-striped table-bordered table-sm`.
- Meaningful colors: `@keyframes edited` flashes a saved cell green `#A6E2A9` for 1.2 s, `edited-error` flashes red
  `#E8AAA4` for 1.6 s. The group action row and footer use `#f9f9f9`.

## States

- Empty: the `noItems` block renders one row with `contributte_datagrid.no_item_found`. With an active filter it
  shows `no_item_found_reset` and a link that resets the filter. The tree view prints the text without a row.
- Loading: the tree view chevron gets `.is-loading` and shows a `fa-spin` spinner. Other requests show no indicator;
  the old content stays until `naja` replaces the snippet.
- Error: a failed cell edit flashes the cell red (`.edited-error`); form validation messages come from Nette Forms.
- Success: a saved cell or inline-edited row flashes green (`.edited`).
- Disabled: group action controls stay `disabled` until at least one row checkbox is checked.
- Confirmation: actions with a confirmation use the browser `window.confirm()` dialog, not a modal.

## Accessibility

- Pagination is a `nav` with `aria-label="Pagination"`; the current page has `aria-current="page"`.
- Sort links, filters and pagination are real links and inputs, reachable by keyboard.
- The column settings dropdown carries `aria-haspopup` and `aria-expanded`; status and multi-action dropdowns do not.
- Known gaps: icon-only buttons (column settings, sort handle, reset column filter) have no text label. The per-column
  menu has `opacity: 0` until the header cell is hovered, so keyboard users cannot see it.

## Dark Mode

- Not supported. `datagrid.css` sets `background-color: #fff` on `[data-datagrid-name]` and fixed light greys, so
  the grid stays light under `data-bs-theme="dark"`.

## Responsive

- One breakpoint in `datagrid.css`: at `min-width: 768px` group action controls render inline in the header row.
- Outer filters stack below Bootstrap's `sm` breakpoint (576 px).
- The table is not wrapped in `.table-responsive`; wide grids overflow unless the host page wraps them.

## Screenshots

- `.docs/assets/datagrid.gif` is the overview shown in `README.md`.
- `.docs/assets/inline_edit.gif`, `inline_edit_2.gif`, `big_inline_edit.gif`, `group_button_action*.gif`,
  `hideable_columns.gif`, `hideable_columns_reset.png`, `status1.gif` and `status2.gif` illustrate the matching
  pages in `.docs/`.
- They are recorded by hand from [datagrid-skeleton](https://github.com/contributte/datagrid-skeleton); record a new
  one when the feature it shows changes.

## Changing the UI

- Block names in `datagrid.latte` and `datagrid_tree.latte` (`data`, `table-class`, `noItems`, `pagination`,
  `icon-*`, `col-{key}`, `col-{key}-header`) are public. Add blocks; never rename or remove them in a minor release.
- Class names and `data-*` attributes are selectors in `assets/`, and app stylesheets use them too. Treat renames
  as breaking and list them in `UPGRADE.md`.
- `dist/` is built from `assets/` with `npm run build` and published to npm by `.github/workflows/assets.yml`; the
  CDN links in `.docs/assets.md` point to `dist/datagrid-full.js` and `dist/datagrid-full.css`.
- Apps that bundle the assets import `vendor/ublaboo/datagrid/assets`; keep the exports in `assets/index.ts` stable.

## Checklist

- [ ] Existing template blocks, CSS classes and `data-*` attributes keep their names
- [ ] New strings use a `contributte_datagrid.*` key with a default in `SimpleTranslator`
- [ ] New icons use `{$iconPrefix}` and sit in an `icon-*` block
- [ ] Links and forms still work without JavaScript
- [ ] `npm run build` passes and the grid looks right at 375 px and 1280 px
- [ ] Screenshots in `.docs/assets/` updated if the UI they show changed
