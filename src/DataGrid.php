<?php declare(strict_types = 1);

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid;

use Nette;
use Nette\Application\UI\Form;
use Nette\Application\UI\Link;
use Nette\Application\UI\PresenterComponent;
use Ublaboo\DataGrid\AggregationFunction\TDataGridAggregationFunction;
use Ublaboo\DataGrid\Column\Column;
use Ublaboo\DataGrid\Exception\DataGridColumnNotFoundException;
use Ublaboo\DataGrid\Exception\DataGridException;
use Ublaboo\DataGrid\Exception\DataGridFilterNotFoundException;
use Ublaboo\DataGrid\Exception\DataGridHasToBeAttachedToPresenterComponentException;
use Ublaboo\DataGrid\Filter\IFilterDate;
use Ublaboo\DataGrid\InlineEdit\InlineEdit;
use Ublaboo\DataGrid\Toolbar\ToolbarButton;
use Ublaboo\DataGrid\Utils\ArraysHelper;
use Ublaboo\DataGrid\Utils\Sorting;

/**
 * @method onRedraw()
 * @method onRender()
 * @method onColumnAdd()
 */
class DataGrid extends Nette\Application\UI\Control
{

	use TDataGridAggregationFunction;

	/**
	 * @var callable[]
	 */
	public $onRedraw;

	/**
	 * @var callable[]
	 */
	public $onRender = [];

	/**
	 * @var callable[]
	 */
	public $onExport;

	/**
	 * @var callable[]
	 */
	public $onColumnAdd;

	/**
	 * @var callable[]
	 * @deprecated use $onFiltersAssembled
	 */
	public $onFiltersAssabled;

	/**
	 * @var callable[]
	 */
	public $onFiltersAssembled;

	/**
	 * @var string
	 */
	public static $icon_prefix = 'fa fa-';

	/**
	 * Default form method
	 *
	 * @var string
	 */
	public static $form_method = 'post';

	/**
	 * When set to TRUE, datagrid throws an exception
	 * 	when tring to get related entity within join and entity does not exist
	 *
	 * @var bool
	 */
	public $strict_entity_property = false;

	/**
	 * When set to TRUE, datagrid throws an exception
	 * 	when tring to set filter value, that does not exist (select, multiselect, etc)
	 *
	 * @var bool
	 */
	public $strict_session_filter_values = true;

	/**
	 * @var int
	 * @persistent
	 */
	public $page = 1;

	/**
	 * @var int|string
	 * @persistent
	 */
	public $per_page;

	/**
	 * @var array
	 * @persistent
	 */
	public $sort = [];

	/**
	 * @var array
	 */
	public $default_sort = [];

	/**
	 * @var array
	 */
	public $default_filter = [];

	/**
	 * @var bool
	 */
	public $default_filter_use_on_reset = true;

	/**
	 * @var bool
	 */
	public $default_sort_use_on_reset = true;

	/**
	 * @var array
	 * @persistent
	 */
	public $filter = [];

	/**
	 * @var callable|null
	 */
	protected $sort_callback = null;

	/**
	 * @var bool
	 */
	protected $use_happy_components = true;

	/**
	 * @var callable
	 */
	protected $rowCallback;

	/**
	 * @var array
	 */
	protected $items_per_page_list;

	/**
	 * @var int
	 */
	protected $default_per_page;

	/**
	 * @var string
	 */
	protected $template_file;

	/**
	 * @var Column\Column[]
	 */
	protected $columns = [];

	/**
	 * @var Column\Action[]
	 */
	protected $actions = [];

	/**
	 * @var GroupAction\GroupActionCollection
	 */
	protected $group_action_collection;

	/**
	 * @var Filter\Filter[]
	 */
	protected $filters = [];

	/**
	 * @var Export\Export[]
	 */
	protected $exports = [];

	/**
	 * @var ToolbarButton[]
	 */
	protected $toolbar_buttons = [];

	/**
	 * @var DataModel
	 */
	protected $dataModel;

	/**
	 * @var DataFilter
	 */
	protected $dataFilter;

	/**
	 * @var string
	 */
	protected $primary_key = 'id';

	/**
	 * @var bool
	 */
	protected $do_paginate = true;

	/**
	 * @var bool
	 */
	protected $csv_export = true;

	/**
	 * @var bool
	 */
	protected $csv_export_filtered = true;

	/**
	 * @var bool
	 */
	protected $sortable = false;

	/**
	 * @var bool
	 */
	protected $multiSort = false;

	/**
	 * @var string
	 */
	protected $sortable_handler = 'sort!';

	/**
	 * @var string
	 */
	protected $original_template;

	/**
	 * @var array
	 */
	protected $redraw_item;

	/**
	 * @var mixed
	 */
	protected $translator;

	/**
	 * @var bool
	 */
	protected $force_filter_active;

	/**
	 * @var callable
	 */
	protected $tree_view_children_callback;

	/**
	 * @var callable
	 */
	protected $tree_view_has_children_callback;

	/**
	 * @var string
	 */
	protected $tree_view_has_children_column;

	/**
	 * @var bool
	 */
	protected $outer_filter_rendering = false;

	/**
	 * @var int
	 */
	protected $outer_filter_columns_count = 2;

	/**
	 * @var bool
	 */
	protected $collapsible_outer_filters = true;

	/**
	 * @var array
	 */
	protected $columns_export_order = [];

	/**
	 * @var bool
	 */
	protected $remember_state = true;

	/**
	 * @var bool
	 */
	protected $refresh_url = true;

	/**
	 * @var Nette\Http\SessionSection
	 */
	protected $grid_session;

	/**
	 * @var Column\ItemDetail
	 */
	protected $items_detail;

	/**
	 * @var array
	 */
	protected $row_conditions = [
		'group_action' => false,
		'action' => [],
	];

	/**
	 * @var array
	 */
	protected $column_callbacks = [];

	/**
	 * @var bool
	 */
	protected $can_hide_columns = false;

	/**
	 * @var array
	 */
	protected $columns_visibility = [];

	/**
	 * @var InlineEdit
	 */
	protected $inlineEdit;

	/**
	 * @var InlineEdit
	 */
	protected $inlineAdd;

	/**
	 * @var bool
	 */
	protected $snippets_set = false;

	/**
	 * @var bool
	 */
	protected $some_column_default_hide = false;

	/**
	 * @var ColumnsSummary
	 */
	protected $columnsSummary;

	/**
	 * @var bool
	 */
	protected $auto_submit = true;

	/**
	 * @var Filter\SubmitButton|NULL
	 */
	protected $filter_submit_button = null;

	/**
	 * @var bool
	 */
	protected $has_column_reset = true;

	/**
	 * @var bool
	 */
	protected $show_selected_rows_count = true;

	/**
	 * @var string
	 */
	private $custom_paginator_template;

	/**
	 * @param Nette\ComponentModel\IContainer|NULL $parent
	 * @param string                               $name
	 */
	public function __construct(?Nette\ComponentModel\IContainer $parent = null, ?string $name = null)
	{
		parent::__construct();

		if ($parent !== null) {
			$parent->addComponent($this, $name);
		}

		$this->monitor('Nette\Application\UI\Presenter');

		/**
		 * Try to find previous filters, pagination, per_page and other values in session
		 */
		$this->onRender[] = [$this, 'findSessionValues'];
		$this->onExport[] = [$this, 'findSessionValues'];

		/**
		 * Find default filter values
		 */
		$this->onRender[] = [$this, 'findDefaultFilter'];
		$this->onExport[] = [$this, 'findDefaultFilter'];

		/**
		 * Find default sort
		 */
		$this->onRender[] = [$this, 'findDefaultSort'];
		$this->onExport[] = [$this, 'findDefaultSort'];

		/**
		 * Find default items per page
		 */
		$this->onRender[] = [$this, 'findDefaultPerPage'];

		/**
		 * Notify about that json js extension
		 */
		$this->onFiltersAssembled[] = [$this, 'sendNonEmptyFiltersInPayload'];
	}


	/**
	 * {inheritDoc}
	 *
	 * @return void
	 */
	public function attached($presenter): void
	{
		parent::attached($presenter);

		if ($presenter instanceof Nette\Application\UI\Presenter) {
			/**
			 * Get session
			 */
			if ($this->remember_state) {
				$this->grid_session = $presenter->getSession($this->getSessionSectionName());
			}
		}
	}


	/********************************************************************************
	 *                                  RENDERING                                   *
	 ********************************************************************************/

/**
 * Render template
 *
 * @return void
 */
	public function render(): void
	{
		/**
		 * Check whether datagrid has set some columns, initiated data source, etc
		 */
		if (!($this->dataModel instanceof DataModel)) {
			throw new DataGridException('You have to set a data source first.');
		}

		if (empty($this->columns)) {
			throw new DataGridException('You have to add at least one column.');
		}

		$template = $this->getTemplate();
		$template->setTranslator($this->getTranslator());

		/**
		 * Invoke possible events
		 */
		$this->onRender($this);

		/**
		 * Prepare data for rendering (datagrid may render just one item)
		 */
		$rows = [];

		if (!empty($this->redraw_item)) {
			$items = $this->dataModel->filterRow($this->redraw_item);
		} else {
			$items = Nette\Utils\Callback::invokeArgs(
				[$this->dataModel, 'filterData'],
				[
					$this->getPaginator(),
					$this->createSorting($this->sort, $this->sort_callback),
					$this->assembleFilters(),
				]
			);
		}

		$callback = $this->rowCallback ?: null;
		$hasGroupActionOnRows = false;

		foreach ($items as $item) {
			$rows[] = $row = new Row($this, $item, $this->getPrimaryKey());

			if (!$hasGroupActionOnRows && $row->hasGroupAction()) {
				$hasGroupActionOnRows = true;
			}

			if ($callback) {
				$callback($item, $row->getControl());
			}

			/**
			 * Walkaround for item snippet - snippet is the <tr> element and its class has to be also updated
			 */
			if (!empty($this->redraw_item)) {
				$this->getPresenter()->payload->_datagrid_redraw_item_class = $row->getControlClass();
				$this->getPresenter()->payload->_datagrid_redraw_item_id = $row->getId();
			}
		}

		if ($hasGroupActionOnRows) {
			$hasGroupActionOnRows = $this->hasGroupActions();
		}

		if ($this->isTreeView()) {
			$template->add('tree_view_has_children_column', $this->tree_view_has_children_column);
		}

		$template->rows = $rows;

		$template->columns = $this->getColumns();
		$template->actions = $this->actions;
		$template->exports = $this->exports;
		$template->filters = $this->filters;
		$template->toolbar_buttons = $this->toolbar_buttons;
		$template->aggregation_functions = $this->getAggregationFunctions();
		$template->multiple_aggregation_function = $this->getMultipleAggregationFunction();

		$template->filter_active = $this->isFilterActive();
		$template->original_template = $this->getOriginalTemplateFile();
		$template->icon_prefix = static::$icon_prefix;
		$template->icon_prefix = static::$icon_prefix;
		$template->items_detail = $this->items_detail;
		$template->columns_visibility = $this->getColumnsVisibility();
		$template->columnsSummary = $this->columnsSummary;

		$template->inlineEdit = $this->inlineEdit;
		$template->inlineAdd = $this->inlineAdd;

		$template->hasGroupActions = $this->hasGroupActions();
		$template->hasGroupActionOnRows = $hasGroupActionOnRows;

		/**
		 * Walkaround for Latte (does not know $form in snippet in {form} etc)
		 */
		$template->filter = $this['filter'];

		/**
		 * Set template file and render it
		 */
		$template->setFile($this->getTemplateFile());
		$template->render();
	}


	/********************************************************************************
	 *                                 ROW CALLBACK                                 *
	 ********************************************************************************/

/**
 * Each row can be modified with user callback
 *
 * @param  callable  $callback
 * @return static
 */
	public function setRowCallback(callable $callback)
	{
		$this->rowCallback = $callback;

		return $this;
	}


	/********************************************************************************
	 *                                 DATA SOURCE                                  *
	 ********************************************************************************/

/**
 * By default ID, you can change that
 *
 * @param string $primary_key
 * @return static
 */
	public function setPrimaryKey(string $primary_key)
	{
		if ($this->dataModel instanceof DataModel) {
			throw new DataGridException('Please set datagrid primary key before setting datasource.');
		}

		$this->primary_key = $primary_key;

		return $this;
	}


	/**
	 * Set Grid data source
	 * @param DataSource\IDataSource|array|\DibiFluent|\Dibi\Fluent|Nette\Database\Table\Selection|\Doctrine\ORM\QueryBuilder|\Nextras\Orm\Collection\ICollection $source
	 * @return static
	 */
	public function setDataSource($source)
	{
		$this->dataModel = new DataModel($source, $this->primary_key);

		$this->dataModel->onBeforeFilter[] = [$this, 'beforeDataModelFilter'];
		$this->dataModel->onAfterFilter[] = [$this, 'afterDataModelFilter'];
		$this->dataModel->onAfterPaginated[] = [$this, 'afterDataModelPaginated'];

		return $this;
	}


	/**
	 * @return DataSource\IDataSource|NULL
	 */
	public function getDataSource(): ?DataSource\IDataSource
	{
		if (!$this->dataModel) {
			return null;
		}

		return $this->dataModel->getDataSource();
	}


	/********************************************************************************
	 *                                  TEMPLATING                                  *
	 ********************************************************************************/

/**
 * Set custom template file to render
 *
 * @param string $template_file
 * @return static
 */
	public function setTemplateFile(string $template_file)
	{
		$this->template_file = $template_file;

		return $this;
	}


	/**
	 * Get DataGrid template file
	 *
	 * @return string
	 * @return static
	 */
	public function getTemplateFile(): string
	{
		return $this->template_file ?: $this->getOriginalTemplateFile();
	}


	/**
	 * Get DataGrid original template file
	 *
	 * @return string
	 */
	public function getOriginalTemplateFile(): string
	{
		return __DIR__ . '/templates/datagrid.latte';
	}


	/**
	 * Tell datagrid wheteher to use or not happy components
	 *
	 * @param  bool|NULL $use If not given, return value of static::$use_happy_components
	 * @return void|bool
	 */
	public function useHappyComponents(?bool $use = null)
	{
		if ($use === null) {
			return $this->use_happy_components;
		}

		$this->use_happy_components = (bool) $use;
	}


	/********************************************************************************
	 *                                   SORTING                                    *
	 ********************************************************************************/

/**
 * Set default sorting
 *
 * @param array $sort
 * @param bool  $use_on_reset
 * @return static
 */
	public function setDefaultSort(array $sort, bool $use_on_reset = true)
	{
		if (is_string($sort)) {
			$sort = [$sort => 'ASC'];
		} else {
			$sort = (array) $sort;
		}

		$this->default_sort = $sort;
		$this->default_sort_use_on_reset = (bool) $use_on_reset;

		return $this;
	}


	/**
	 * Return default sort for column, if specified
	 *
	 * @param string $columnKey
	 * @return string|NULL
	 */
	public function getColumnDefaultSort(string $columnKey): ?string
	{
		if (isset($this->default_sort[$columnKey])) {
			return $this->default_sort[$columnKey];
		}

		return null;
	}


	/**
	 * User may set default sorting, apply it
	 *
	 * @return void
	 */
	public function findDefaultSort(): void
	{
		if (!empty($this->sort)) {
			return;
		}

		if ($this->getSessionData('_grid_has_sorted')) {
			return;
		}

		if (!empty($this->default_sort)) {
			$this->sort = $this->default_sort;
		}

		$this->saveSessionData('_grid_sort', $this->sort);
	}


	/**
	 * Set grido to be sortable
	 *
	 * @param bool $sortable
	 * @return static
	 */
	public function setSortable(bool $sortable = true)
	{
		if ($this->getItemsDetail()) {
			throw new DataGridException('You can not use both sortable datagrid and items detail.');
		}

		$this->sortable = (bool) $sortable;

		return $this;
	}


	/**
	 * Tell whether DataGrid is sortable
	 *
	 * @return bool
	 */
	public function isSortable(): bool
	{
		return $this->sortable;
	}


	/**
	 * Enable multi-sorting capability
	 *
	 * @param bool  $multiSort
	 * @return static
	 */
	public function setMultiSortEnabled(bool $multiSort = true)
	{
		$this->multiSort = (bool) $multiSort;

		return $this;
	}


	/**
	 * Tell wether DataGrid can be sorted by multiple columns
	 *
	 * @return bool
	 */
	public function isMultiSortEnabled(): bool
	{
		return $this->multiSort;
	}


	/**
	 * Set sortable handle
	 *
	 * @param string $handler
	 * @return static
	 */
	public function setSortableHandler(string $handler = 'sort!')
	{
		$this->sortable_handler = (string) $handler;

		return $this;
	}


	/**
	 * Return sortable handle name
	 *
	 * @return string
	 */
	public function getSortableHandler(): string
	{
		return $this->sortable_handler;
	}


	/**
	 * @param Column  $column
	 * @return array
	 * @internal
	 */
	public function getSortNext(Column $column): array
	{
		$sort = $column->getSortNext();

		if ($this->isMultiSortEnabled()) {
			$sort = array_merge($this->sort, $sort);
		}

		return array_filter($sort);
	}


	/**
	 * @param  array         $sort
	 * @param  callable|NULL $sort_callback
	 * @return Sorting
	 */
	protected function createSorting(array $sort, ?callable $sort_callback = null): Sorting
	{
		foreach ($sort as $key => $order) {
			unset($sort[$key]);

			try {
				$column = $this->getColumn($key);

			} catch (DataGridColumnNotFoundException $e) {
				continue;
			}

			$sort[$column->getSortingColumn()] = $order;
		}

		if (!$sort_callback && isset($column)) {
			$sort_callback = $column->getSortableCallback();
		}

		return new Sorting($sort, $sort_callback);
	}


	/********************************************************************************
	 *                                  TREE VIEW                                   *
	 ********************************************************************************/

/**
 * Is tree view set?
 *
 * @return bool
 */
	public function isTreeView(): bool
	{
		return (bool) $this->tree_view_children_callback;
	}


	/**
	 * Setting tree view
	 *
	 * @param callable $get_children_callback
	 * @param string|callable $tree_view_has_children_column
	 * @return static
	 */
	public function setTreeView(callable $get_children_callback, $tree_view_has_children_column = 'has_children')
	{
		if (!is_callable($get_children_callback)) {
			throw new DataGridException(
				'Parameters to method DataGrid::setTreeView must be of type callable'
			);
		}

		if (is_callable($tree_view_has_children_column)) {
			$this->tree_view_has_children_callback = $tree_view_has_children_column;
			$tree_view_has_children_column = null;
		}

		$this->tree_view_children_callback = $get_children_callback;
		$this->tree_view_has_children_column = $tree_view_has_children_column;

		/**
		 * TUrn off pagination
		 */
		$this->setPagination(false);

		/**
		 * Set tree view template file
		 */
		if (!$this->template_file) {
			$this->setTemplateFile(__DIR__ . '/templates/datagrid_tree.latte');
		}

		return $this;
	}


	/**
	 * Is tree view children callback set?
	 *
	 * @return bool
	 */
	public function hasTreeViewChildrenCallback(): bool
	{
		return is_callable($this->tree_view_has_children_callback);
	}


	/**
	 * @param  mixed $item
	 * @return bool
	 */
	public function treeViewChildrenCallback($item): bool
	{
		return call_user_func($this->tree_view_has_children_callback, $item);
	}


	/********************************************************************************
	 *                                    COLUMNS                                   *
	 ********************************************************************************/

/**
 * Add text column with no other formating
 *
 * @param  string      $key
 * @param  string      $name
 * @param  string|null $column
 * @return Column\ColumnText
 */
	public function addColumnText(string $key, string $name, ?string $column = null): Column\ColumnText
	{
		$this->addColumnCheck($key);
		$column = $column ?: $key;

		return $this->addColumn($key, new Column\ColumnText($this, $key, $column, $name));
	}


	/**
	 * Add column with link
	 *
	 * @param  string      $key
	 * @param  string      $name
	 * @param  string|null $column
	 * @return Column\ColumnLink
	 */
	public function addColumnLink(string $key, string $name, $href = null, ?string $column = null, ?array $params = null): Column\ColumnLink
	{
		$this->addColumnCheck($key);
		$column = $column ?: $key;
		$href = $href ?: $key;

		if ($params === null) {
			$params = [$this->primary_key];
		}

		return $this->addColumn($key, new Column\ColumnLink($this, $key, $column, $name, $href, $params));
	}


	/**
	 * Add column with possible number formating
	 *
	 * @param  string      $key
	 * @param  string      $name
	 * @param  string|null $column
	 * @return Column\ColumnNumber
	 */
	public function addColumnNumber(string $key, string $name, ?string $column = null): Column\ColumnNumber
	{
		$this->addColumnCheck($key);
		$column = $column ?: $key;

		return $this->addColumn($key, new Column\ColumnNumber($this, $key, $column, $name));
	}


	/**
	 * Add column with date formating
	 *
	 * @param  string      $key
	 * @param  string      $name
	 * @param  string|null $column
	 * @return Column\ColumnDateTime
	 */
	public function addColumnDateTime(string $key, string $name, ?string $column = null): Column\ColumnDateTime
	{
		$this->addColumnCheck($key);
		$column = $column ?: $key;

		return $this->addColumn($key, new Column\ColumnDateTime($this, $key, $column, $name));
	}


	/**
	 * Add column status
	 *
	 * @param  string      $key
	 * @param  string      $name
	 * @param  string|null $column
	 * @return Column\ColumnStatus
	 */
	public function addColumnStatus(string $key, string $name, ?string $column = null): Column\ColumnStatus
	{
		$this->addColumnCheck($key);
		$column = $column ?: $key;

		return $this->addColumn($key, new Column\ColumnStatus($this, $key, $column, $name));
	}


	/**
	 * @param string $key
	 * @param Column\Column $column
	 * @return Column\Column
	 */
	protected function addColumn(string $key, Column\Column $column): Column\Column
	{
		$this->onColumnAdd($key, $column);

		$this->columns_visibility[$key] = [
			'visible' => true,
		];

		return $this->columns[$key] = $column;
	}


	/**
	 * Return existing column
	 *
	 * @param  string $key
	 * @return Column\Column
	 * @throws DataGridException
	 */
	public function getColumn(string $key): Column\Column
	{
		if (!isset($this->columns[$key])) {
			throw new DataGridColumnNotFoundException("There is no column at key [$key] defined.");
		}

		return $this->columns[$key];
	}


	/**
	 * Remove column
	 *
	 * @param string $key
	 * @return static
	 */
	public function removeColumn(string $key)
	{
		unset($this->columns_visibility[$key], $this->columns[$key]);

		return $this;
	}


	/**
	 * Check whether given key already exists in $this->columns
	 *
	 * @param  string $key
	 * @throws DataGridException
	 */
	protected function addColumnCheck(string $key): void
	{
		if (isset($this->columns[$key])) {
			throw new DataGridException("There is already column at key [$key] defined.");
		}
	}


	/********************************************************************************
	 *                                    ACTIONS                                   *
	 ********************************************************************************/

/**
 * Create action
 *
 * @param string     $key
 * @param string     $name
 * @param string     $href
 * @param array|null $params
 * @return Column\Action
 */
	public function addAction(string $key, string $name, ?string $href = null, ?array $params = null): Column\Action
	{
		$this->addActionCheck($key);

		$href = $href ?: $key;

		if ($params === null) {
			$params = [$this->primary_key];
		}

		return $this->actions[$key] = new Column\Action($this, $href, $name, $params);
	}


	/**
	 * Create action callback
	 *
	 * @param string     $key
	 * @param string     $name
	 * @return Column\Action
	 */
	public function addActionCallback(string $key, string $name, $callback = null): Column\Action
	{
		$this->addActionCheck($key);

		$params = ['__id' => $this->primary_key];

		$this->actions[$key] = $action = new Column\ActionCallback($this, $key, $name, $params);

		if ($callback) {
			if (!is_callable($callback)) {
				throw new DataGridException('ActionCallback callback has to be callable.');
			}

			$action->onClick[] = $callback;
		}

		return $action;
	}


	/**
	 * @param string $key
	 */
	public function addMultiAction(string $key, $name)
	{
		$this->addActionCheck($key);

		$this->actions[$key] = $action = new Column\MultiAction($this, $name);

		return $action;
	}


	/**
	 * Get existing action
	 *
	 * @param  string       $key
	 * @return Column\Action
	 * @throws DataGridException
	 */
	public function getAction(string $key): Column\Action
	{
		if (!isset($this->actions[$key])) {
			throw new DataGridException("There is no action at key [$key] defined.");
		}

		return $this->actions[$key];
	}


	/**
	 * Remove action
	 *
	 * @param string $key
	 * @return static
	 */
	public function removeAction(string $key)
	{
		unset($this->actions[$key]);

		return $this;
	}


	/**
	 * Check whether given key already exists in $this->filters
	 *
	 * @param  string $key
	 * @throws DataGridException
	 */
	protected function addActionCheck(string $key): void
	{
		if (isset($this->actions[$key])) {
			throw new DataGridException("There is already action at key [$key] defined.");
		}
	}


	/********************************************************************************
	 *                                    FILTERS                                   *
	 ********************************************************************************/

/**
 * Add filter fot text search
 *
 * @param string       $key
 * @param string       $name
 * @param array|string $columns
 * @return Filter\FilterText
 * @throws DataGridException
 */
	public function addFilterText(string $key, string $name, $columns = null): Filter\FilterText
	{
		$columns = $columns === null ? [$key] : (is_string($columns) ? [$columns] : $columns);

		if (!is_array($columns)) {
			throw new DataGridException('Filter Text can accept only array or string.');
		}

		$this->addFilterCheck($key);

		return $this->filters[$key] = new Filter\FilterText($this, $key, $name, $columns);
	}


	/**
	 * Add select box filter
	 *
	 * @param string $key
	 * @param string $name
	 * @param array  $options
	 * @param string $column
	 * @return Filter\FilterSelect
	 * @throws DataGridException
	 */
	public function addFilterSelect(string $key, string $name, array $options, ?string $column = null): Filter\FilterSelect
	{
		$column = $column ?: $key;

		if (!is_string($column)) {
			throw new DataGridException('Filter Select can only filter in one column.');
		}

		$this->addFilterCheck($key);

		return $this->filters[$key] = new Filter\FilterSelect($this, $key, $name, $options, $column);
	}


	/**
	 * Add multi select box filter
	 *
	 * @param string $key
	 * @param string $name
	 * @param array  $options
	 * @param string $column
	 * @return Filter\FilterSelect
	 * @throws DataGridException
	 */
	public function addFilterMultiSelect(string $key, string $name, array $options, ?string $column = null): Filter\FilterSelect
	{
		$column = $column ?: $key;

		if (!is_string($column)) {
			throw new DataGridException('Filter MultiSelect can only filter in one column.');
		}

		$this->addFilterCheck($key);

		return $this->filters[$key] = new Filter\FilterMultiSelect($this, $key, $name, $options, $column);
	}


	/**
	 * Add datepicker filter
	 *
	 * @param string $key
	 * @param string $name
	 * @param string $column
	 * @return Filter\FilterDate
	 * @throws DataGridException
	 */
	public function addFilterDate(string $key, string $name, ?string $column = null): Filter\FilterDate
	{
		$column = $column ?: $key;

		if (!is_string($column)) {
			throw new DataGridException('FilterDate can only filter in one column.');
		}

		$this->addFilterCheck($key);

		return $this->filters[$key] = new Filter\FilterDate($this, $key, $name, $column);
	}


	/**
	 * Add range filter (from - to)
	 *
	 * @param string $key
	 * @param string $name
	 * @param string $column
	 * @return Filter\FilterRange
	 * @throws DataGridException
	 */
	public function addFilterRange(string $key, string $name, ?string $column = null, $name_second = '-'): Filter\FilterRange
	{
		$column = $column ?: $key;

		if (!is_string($column)) {
			throw new DataGridException('FilterRange can only filter in one column.');
		}

		$this->addFilterCheck($key);

		return $this->filters[$key] = new Filter\FilterRange($this, $key, $name, $column, $name_second);
	}


	/**
	 * Add datepicker filter (from - to)
	 *
	 * @param string $key
	 * @param string $name
	 * @param string $column
	 * @return Filter\FilterDateRange
	 * @throws DataGridException
	 */
	public function addFilterDateRange(string $key, string $name, ?string $column = null, $name_second = '-'): Filter\FilterDateRange
	{
		$column = $column ?: $key;

		if (!is_string($column)) {
			throw new DataGridException('FilterDateRange can only filter in one column.');
		}

		$this->addFilterCheck($key);

		return $this->filters[$key] = new Filter\FilterDateRange($this, $key, $name, $column, $name_second);
	}


	/**
	 * Check whether given key already exists in $this->filters
	 *
	 * @param  string $key
	 * @throws DataGridException
	 */
	protected function addFilterCheck(string $key): void
	{
		if (isset($this->filters[$key])) {
			throw new DataGridException("There is already action at key [$key] defined.");
		}
	}


	/**
	 * Fill array of Filter\Filter[] with values from $this->filter persistent parameter
	 * Fill array of Column\Column[] with values from $this->sort   persistent parameter
	 *
	 * @return Filter\Filter[] $this->filters === Filter\Filter[]
	 */
	public function assembleFilters()
	{
		foreach ($this->filter as $key => $value) {
			if (!isset($this->filters[$key])) {
				$this->deleteSessionData($key);

				continue;
			}

			if (is_array($value) || $value instanceof \Traversable) {
				if (!ArraysHelper::testEmpty($value)) {
					$this->filters[$key]->setValue($value);
				}
			} else {
				if ($value !== '' && $value !== null) {
					$this->filters[$key]->setValue($value);
				}
			}
		}

		foreach ($this->columns as $key => $column) {
			if (isset($this->sort[$key])) {
				$column->setSort($this->sort);
			}
		}

		/**
		 * Invoke possible events
		 */
		if (!empty($this->onFiltersAssabled)) {
			@trigger_error('onFiltersAssabled is deprecated, use onFiltersAssembled instead', E_USER_DEPRECATED);
			$this->onFiltersAssabled($this->filters);
		}

		$this->onFiltersAssembled($this->filters);
		return $this->filters;
	}


	/**
	 * Fill array of Filter\Filter[] with values from $this->filter persistent parameter
	 * Fill array of Column\Column[] with values from $this->sort   persistent parameter
	 *
	 * @return Filter\Filter[] $this->filters === Filter\Filter[]
	 * @deprecated use assembleFilters instead
	 */
	public function assableFilters()
	{
		@trigger_error('assableFilters is deprecated, use assembleFilters instead', E_USER_DEPRECATED);
		return $this->assembleFilters();
	}


	/**
	 * Remove filter
	 *
	 * @param string $key
	 * @return static
	 */
	public function removeFilter(string $key)
	{
		unset($this->filters[$key]);

		return $this;
	}


	/**
	 * Get defined filter
	 *
	 * @param  string $key
	 * @return Filter\Filter
	 */
	public function getFilter(string $key): Filter\Filter
	{
		if (!isset($this->filters[$key])) {
			throw new DataGridException("Filter [{$key}] is not defined");
		}

		return $this->filters[$key];
	}


	/**
	 * @param bool $strict
	 * @return static
	 */
	public function setStrictSessionFilterValues(bool $strict = true)
	{
		$this->strict_session_filter_values = (bool) $strict;

		return $this;
	}


	/********************************************************************************
	 *                                  FILTERING                                   *
	 ********************************************************************************/

/**
 * Is filter active?
 *
 * @return bool
 */
	public function isFilterActive(): bool
	{
		$is_filter = ArraysHelper::testTruthy($this->filter);

		return ($is_filter) || $this->force_filter_active;
	}


	/**
	 * Tell that filter is active from whatever reasons
	 * return static
	 */
	public function setFilterActive()
	{
		$this->force_filter_active = true;

		return $this;
	}


	/**
	 * Set filter values (force - overwrite user data)
	 *
	 * @param array $filter
	 * @return static
	 */
	public function setFilter(array $filter)
	{
		$this->filter = $filter;

		$this->saveSessionData('_grid_has_filtered', 1);

		return $this;
	}


	/**
	 * If we want to sent some initial filter
	 *
	 * @param array $filter
	 * @param bool  $use_on_reset
	 * @return static
	 */
	public function setDefaultFilter(array $default_filter, bool $use_on_reset = true)
	{
		foreach ($default_filter as $key => $value) {
			$filter = $this->getFilter($key);

			if (!$filter) {
				throw new DataGridException("Can not set default value to nonexisting filter [$key]");
			}

			if ($filter instanceof Filter\FilterMultiSelect && !is_array($value)) {
				throw new DataGridException(
					"Default value of filter [$key] - MultiSelect has to be an array"
				);
			}

			if ($filter instanceof Filter\FilterRange || $filter instanceof Filter\FilterDateRange) {
				if (!is_array($value)) {
					throw new DataGridException(
						"Default value of filter [$key] - Range/DateRange has to be an array [from/to => ...]"
					);
				}

				$temp = $value;
				unset($temp['from'], $temp['to']);

				if (!empty($temp)) {
					throw new DataGridException(
						"Default value of filter [$key] - Range/DateRange can contain only [from/to => ...] values"
					);
				}
			}
		}

		$this->default_filter = $default_filter;
		$this->default_filter_use_on_reset = (bool) $use_on_reset;

		return $this;
	}


	/**
	 * User may set default filter, find it
	 *
	 * @return void
	 */
	public function findDefaultFilter(): void
	{
		if (!empty($this->filter)) {
			return;
		}

		if ($this->getSessionData('_grid_has_filtered')) {
			return;
		}

		if (!empty($this->default_filter)) {
			$this->filter = $this->default_filter;
		}

		foreach ($this->filter as $key => $value) {
			$this->saveSessionData($key, $value);
		}
	}


	/**
	 * FilterAndGroupAction form factory
	 *
	 * @return Form
	 */
	public function createComponentFilter()
	{
		$form = new Form($this, 'filter');

		$form->setMethod(static::$form_method);

		$form->setTranslator($this->getTranslator());

		/**
		 * InlineEdit part
		 */
		$inline_edit_container = $form->addContainer('inline_edit');

		if ($this->inlineEdit instanceof InlineEdit) {
			$inline_edit_container->addSubmit('submit', 'ublaboo_datagrid.save')
				->setValidationScope([$inline_edit_container]);
			$inline_edit_container->addSubmit('cancel', 'ublaboo_datagrid.cancel')
				->setValidationScope(false);

			$this->inlineEdit->onControlAdd($inline_edit_container);
			$this->inlineEdit->onControlAfterAdd($inline_edit_container);
		}

		/**
		 * InlineAdd part
		 */
		$inline_add_container = $form->addContainer('inline_add');

		if ($this->inlineAdd instanceof InlineEdit) {
			$inline_add_container->addSubmit('submit', 'ublaboo_datagrid.save')
				->setValidationScope([$inline_add_container]);
			$inline_add_container->addSubmit('cancel', 'ublaboo_datagrid.cancel')
				->setValidationScope(false)
				->setAttribute('data-datagrid-cancel-inline-add', true);

			$this->inlineAdd->onControlAdd($inline_add_container);
			$this->inlineAdd->onControlAfterAdd($inline_add_container);
		}

		/**
		 * ItemDetail form part
		 */
		$items_detail_form = $this->getItemDetailForm();

		if ($items_detail_form instanceof Nette\Forms\Container) {
			$form['items_detail_form'] = $items_detail_form;
		}

		/**
		 * Filter part
		 */
		$filter_container = $form->addContainer('filter');

		foreach ($this->filters as $filter) {
			$filter->addToFormContainer($filter_container);
		}

		if (!$this->hasAutoSubmit()) {
			$filter_container['submit'] = $this->getFilterSubmitButton();
		}

		/**
		 * Group action part
		 */
		$group_action_container = $form->addContainer('group_action');

		if ($this->hasGroupActions()) {
			$this->getGroupActionCollection()->addToFormContainer($group_action_container);
		}

		if (!$form->isSubmitted()) {
			$this->setFilterContainerDefaults($form['filter'], $this->filter);
		}

		/**
		 * Per page part
		 */
		$form->addSelect('per_page', '', $this->getItemsPerPageList())
			->setTranslator(null);

		if (!$form->isSubmitted()) {
			$form['per_page']->setValue($this->getPerPage());
		}

		$form->addSubmit('per_page_submit', 'ublaboo_datagrid.per_page_submit')
			->setValidationScope([$form['per_page']]);

		$form->onSubmit[] = [$this, 'filterSucceeded'];
	}


	/**
	 * @param  Nette\Forms\Container  $container
	 * @param  array|\Iterator  $values
	 * @return void
	 */
	public function setFilterContainerDefaults(Nette\Forms\Container $container, $values): void
	{
		foreach ($container->getComponents() as $key => $control) {
			if (!isset($values[$key])) {
				continue;
			}

			if ($control instanceof Nette\Forms\Container) {
				$this->setFilterContainerDefaults($control, $values[$key]);

				continue;
			}

			$value = $values[$key];

			if ($value instanceof \DateTime && ($filter = $this->getFilter($key)) instanceof IFilterDate) {
				$value = $value->format($filter->getPhpFormat());
			}

			try {
				$control->setValue($value);

			} catch (Nette\InvalidArgumentException $e) {
				if ($this->strict_session_filter_values) {
					throw $e;
				}
			}
		}
	}


	/**
	 * Set $this->filter values after filter form submitted
	 *
	 * @param  Form $form
	 * @return void
	 */
	public function filterSucceeded(Form $form): void
	{
		if ($this->snippets_set) {
			return;
		}

		$values = $form->getValues();

		if ($this->getPresenter()->isAjax()) {
			if (isset($form['group_action']['submit']) && $form['group_action']['submit']->isSubmittedBy()) {
				return;
			}
		}

		/**
		 * Per page
		 */
		$this->saveSessionData('_grid_per_page', $values->per_page);
		$this->per_page = $values->per_page;

		/**
		 * Inline edit
		 */
		if (isset($form['inline_edit']) && isset($form['inline_edit']['submit']) && isset($form['inline_edit']['cancel'])) {
			$edit = $form['inline_edit'];

			if ($edit['submit']->isSubmittedBy() || $edit['cancel']->isSubmittedBy()) {
				$id = $form->getHttpData(Form::DATA_LINE, 'inline_edit[_id]');
				$primary_where_column = $form->getHttpData(
					Form::DATA_LINE,
					'inline_edit[_primary_where_column]'
				);

				if ($edit['submit']->isSubmittedBy() && !$edit->getErrors()) {
					$this->inlineEdit->onSubmit($id, $values->inline_edit);
					$this->getPresenter()->payload->_datagrid_inline_edited = $id;
					$this->getPresenter()->payload->_datagrid_name = $this->getName();
				} else {
					$this->getPresenter()->payload->_datagrid_inline_edit_cancel = $id;
					$this->getPresenter()->payload->_datagrid_name = $this->getName();
				}

				if ($edit['submit']->isSubmittedBy() && !empty($this->inlineEdit->onCustomRedraw)) {
					$this->inlineEdit->onCustomRedraw();
				} else {
					$this->redrawItem($id, $primary_where_column);
					$this->redrawControl('summary');
				}

				return;
			}
		}

		/**
		 * Inline add
		 */
		if (isset($form['inline_add']) && isset($form['inline_add']['submit']) && isset($form['inline_add']['cancel'])) {
			$add = $form['inline_add'];

			if ($add['submit']->isSubmittedBy() || $add['cancel']->isSubmittedBy()) {
				if ($add['submit']->isSubmittedBy() && !$add->getErrors()) {
					$this->inlineAdd->onSubmit($values->inline_add);

					if ($this->getPresenter()->isAjax()) {
						$this->getPresenter()->payload->_datagrid_inline_added = true;
					}
				}

				return;
			}
		}

		/**
		 * Filter itself
		 */
		$values = $values['filter'];

		foreach ($values as $key => $value) {
			/**
			 * Session stuff
			 */
			if ($this->remember_state && $this->getSessionData($key) !== $value) {
				/**
				 * Has been filter changed?
				 */
				$this->page = 1;
				$this->saveSessionData('_grid_page', 1);
			}

			$this->saveSessionData($key, $value);

			/**
			 * Other stuff
			 */
			$this->filter[$key] = $value;
		}

		if (!empty($values)) {
			$this->saveSessionData('_grid_has_filtered', 1);
		}

		if ($this->getPresenter()->isAjax()) {
			$this->getPresenter()->payload->_datagrid_sort = [];

			foreach ($this->columns as $key => $column) {
				if ($column->isSortable()) {
					$this->getPresenter()->payload->_datagrid_sort[$key] = $this->link('sort!', [
						'sort' => $column->getSortNext(),
					]);
				}
			}
		}

		$this->reload();
	}


	/**
	 * Should be datagrid filters rendered separately?
	 *
	 * @param bool $out
	 * @return static
	 */
	public function setOuterFilterRendering(bool $out = true)
	{
		$this->outer_filter_rendering = (bool) $out;

		return $this;
	}


	/**
	 * Are datagrid filters rendered separately?
	 *
	 * @return bool
	 */
	public function hasOuterFilterRendering(): bool
	{
		return $this->outer_filter_rendering;
	}


	/**
	 * Set the number of columns in the outer filter
	 *
	 * @param int $count
	 * @return static
	 * @throws \InvalidArgumentException
	 */
	public function setOuterFilterColumnsCount(int $count)
	{
		if (!in_array($count, [1, 2, 3, 4, 6, 12], true)) {
			throw new \InvalidArgumentException(
				"Columns count must be one of following values: 1, 2, 3, 4, 6, 12. Value {$count} given."
			);
		}

		$this->outer_filter_columns_count = (int) $count;

		return $this;
	}


	/**
	 * @return bool
	 */
	public function getOuterFilterColumnsCount(): bool
	{
		return $this->outer_filter_columns_count;
	}


	/**
	 * @param bool $collapsible_outer_filters
	 */
	public function setCollapsibleOuterFilters(bool $collapsible_outer_filters = true): void
	{
		$this->collapsible_outer_filters = (bool) $collapsible_outer_filters;
	}


	/**
	 * @return bool
	 */
	public function hasCollapsibleOuterFilters(): bool
	{
		return $this->collapsible_outer_filters;
	}


	/**
	 * Try to restore session stuff
	 *
	 * @return void
	 * @throws DataGridFilterNotFoundException
	 */
	public function findSessionValues(): void
	{
		if (!ArraysHelper::testEmpty($this->filter) || ($this->page !== 1) || !empty($this->sort)) {
			return;
		}

		if (!$this->remember_state) {
			return;
		}

		if ($page = $this->getSessionData('_grid_page')) {
			$this->page = $page;
		}

		if ($per_page = $this->getSessionData('_grid_per_page')) {
			$this->per_page = $per_page;
		}

		if ($sort = $this->getSessionData('_grid_sort')) {
			$this->sort = $sort;
		}

		foreach ($this->getSessionData() as $key => $value) {
			$other_session_keys = [
				'_grid_per_page',
				'_grid_sort',
				'_grid_page',
				'_grid_has_sorted',
				'_grid_has_filtered',
				'_grid_hidden_columns',
				'_grid_hidden_columns_manipulated',
			];

			if (!in_array($key, $other_session_keys, true)) {
				try {
					$this->getFilter($key);

					$this->filter[$key] = $value;

				} catch (DataGridException $e) {
					if ($this->strict_session_filter_values) {
						throw new DataGridFilterNotFoundException("Session filter: Filter [$key] not found");
					}
				}
			}
		}

		/**
		 * When column is sorted via custom callback, apply it
		 */
		if (empty($this->sort_callback) && !empty($this->sort)) {
			foreach ($this->sort as $key => $order) {
				try {
					$column = $this->getColumn($key);

				} catch (DataGridColumnNotFoundException $e) {
					$this->deleteSessionData('_grid_sort');
					$this->sort = [];

					return;
				}

				if ($column && $column->isSortable() && is_callable($column->getSortableCallback())) {
					$this->sort_callback = $column->getSortableCallback();
				}
			}
		}
	}


	/********************************************************************************
	 *                                    EXPORTS                                   *
	 ********************************************************************************/

/**
 * Add export of type callback
 *
 * @param string $text
 * @param callable $callback
 * @param bool $filtered
 * @return Export\Export
 */
	public function addExportCallback(string $text, callable $callback, bool $filtered = false): Export\Export
	{
		if (!is_callable($callback)) {
			throw new DataGridException('Second parameter of ExportCallback must be callable.');
		}

		return $this->addToExports(new Export\Export($this, $text, $callback, $filtered));
	}


	/**
	 * Add already implemented csv export
	 *
	 * @param string $text
	 * @param string $csv_file_name
	 * @param string|null $output_encoding
	 * @param string|null $delimiter
	 * @param bool $include_bom
	 * @return Export\Export
	 */
	public function addExportCsv(
		string $text,
		string $csv_file_name,
		?string $output_encoding = null,
		?string $delimiter = null,
		bool $include_bom = false
	): Export\Export
	{
		return $this->addToExports(new Export\ExportCsv(
			$this,
			$text,
			$csv_file_name,
			false,
			$output_encoding,
			$delimiter,
			$include_bom
		));
	}


	/**
	 * Add already implemented csv export, but for filtered data
	 *
	 * @param string $text
	 * @param string $csv_file_name
	 * @param string|null $output_encoding
	 * @param string|null $delimiter
	 * @param bool $include_bom
	 * @return Export\Export
	 */
	public function addExportCsvFiltered(
		string $text,
		string $csv_file_name,
		?string $output_encoding = null,
		?string $delimiter = null,
		bool $include_bom = false
	): Export\Export
	{
		return $this->addToExports(new Export\ExportCsv(
			$this,
			$text,
			$csv_file_name,
			true,
			$output_encoding,
			$delimiter,
			$include_bom
		));
	}


	/**
	 * Add export to array
	 *
	 * @param Export\Export $export
	 * @return Export\Export
	 */
	protected function addToExports(Export\Export $export): Export\Export
	{
		$id = ($s = sizeof($this->exports)) ? ($s + 1) : 1;

		$export->setLink(new Link($this, 'export!', ['id' => $id]));

		return $this->exports[$id] = $export;
	}


	/**
	 * @return void
	 */
	public function resetExportsLinks(): void
	{
		foreach ($this->exports as $id => $export) {
			$export->setLink(new Link($this, 'export!', ['id' => $id]));
		}
	}


	/********************************************************************************
	 *                                TOOLBAR BUTTONS                               *
	 ********************************************************************************/

/**
 * Add toolbar button
 *
 * @param  string  $href
 * @param  string  $text
 * @param  array   $params
 * @return ToolbarButton
 * @throws DataGridException
 */
	public function addToolbarButton(string $href, string $text = '', array $params = []): ToolbarButton
	{
		if (isset($this->toolbar_buttons[$href])) {
			throw new DataGridException("There is already toolbar button at key [$href] defined.");
		}

		return $this->toolbar_buttons[$href] = new ToolbarButton($this, $href, $text, $params);
	}


	/**
	 * Get existing toolbar button
	 *
	 * @param  string  $key
	 * @return ToolbarButton
	 * @throws DataGridException
	 */
	public function getToolbarButton(string $key): ToolbarButton
	{
		if (!isset($this->toolbar_buttons[$key])) {
			throw new DataGridException("There is no toolbar button at key [$key] defined.");
		}

		return $this->toolbar_buttons[$key];
	}


	/**
	 * Remove toolbar button.
	 *
	 * @param  string $key
	 * @return static
	 */
	public function removeToolbarButton(string $key)
	{
		unset($this->toolbar_buttons[$key]);

		return $this;
	}


	/********************************************************************************
	 *                                 GROUP ACTIONS                                *
	 ********************************************************************************/

/**
 * Alias for add group select action
 *
 * @param string $title
 * @param array  $options
 * @return GroupAction\GroupAction
 */
	public function addGroupAction(string $title, array $options = []): GroupAction\GroupAction
	{
		return $this->getGroupActionCollection()->addGroupSelectAction($title, $options);
	}


	/**
	 * Add group action (select box)
	 *
	 * @param string $title
	 * @param array  $options
	 * @return GroupAction\GroupAction
	 */
	public function addGroupSelectAction(string $title, array $options = []): GroupAction\GroupAction
	{
		return $this->getGroupActionCollection()->addGroupSelectAction($title, $options);
	}


	/**
	 * Add group action (multiselect box)
	 *
	 * @param string $title
	 * @param array  $options
	 * @return GroupAction\GroupAction
	 */
	public function addGroupMultiSelectAction(string $title, array $options = []): GroupAction\GroupAction
	{
		return $this->getGroupActionCollection()->addGroupMultiSelectAction($title, $options);
	}


	/**
	 * Add group action (text input)
	 *
	 * @param string $title
	 * @return GroupAction\GroupAction
	 */
	public function addGroupTextAction(string $title): GroupAction\GroupAction
	{
		return $this->getGroupActionCollection()->addGroupTextAction($title);
	}


	/**
	 * Add group action (textarea)
	 *
	 * @param string $title
	 * @return GroupAction\GroupAction
	 */
	public function addGroupTextareaAction(string $title): GroupAction\GroupAction
	{
		return $this->getGroupActionCollection()->addGroupTextareaAction($title);
	}


	/**
	 * Get collection of all group actions
	 *
	 * @return GroupAction\GroupActionCollection
	 */
	public function getGroupActionCollection(): GroupAction\GroupActionCollection
	{
		if (!$this->group_action_collection) {
			$this->group_action_collection = new GroupAction\GroupActionCollection($this);
		}

		return $this->group_action_collection;
	}


	/**
	 * Has datagrid some group actions?
	 *
	 * @return bool
	 */
	public function hasGroupActions(): bool
	{
		return (bool) $this->group_action_collection;
	}


	/**
	 * @return bool
	 */
	public function shouldShowSelectedRowsCount(): bool
	{
		return $this->show_selected_rows_count;
	}


	/**
	 * @return static
	 */
	public function setShowSelectedRowsCount($show = true)
	{
		$this->show_selected_rows_count = (bool) $show;

		return $this;
	}


	/********************************************************************************
	 *                                   HANDLERS                                   *
	 ********************************************************************************/

/**
 * Handler for changind page (just refresh site with page as persistent paramter set)
 *
 * @param  int  $page
 * @return void
 */
	public function handlePage(int $page): void
	{
		/**
		 * Session stuff
		 */
		$this->page = $page;
		$this->saveSessionData('_grid_page', $page);

		$this->reload(['table']);
	}


	/**
	 * Handler for sorting
	 *
	 * @param array $sort
	 * @return void
	 * @throws DataGridColumnNotFoundException
	 */
	public function handleSort(array $sort): void
	{
		if (count($sort) === 0) {
			$sort = $this->default_sort;
		}

		foreach ($sort as $key => $value) {
			try {
				$column = $this->getColumn($key);

			} catch (DataGridColumnNotFoundException $e) {
				unset($sort[$key]);
				continue;
			}

			if ($column->sortableResetPagination()) {
				$this->saveSessionData('_grid_page', $this->page = 1);
			}

			if ($column->getSortableCallback()) {
				$this->sort_callback = $column->getSortableCallback();
			}
		}

		$this->saveSessionData('_grid_has_sorted', 1);
		$this->saveSessionData('_grid_sort', $this->sort = $sort);

		$this->reloadTheWholeGrid();
	}


	/**
	 * Handler for reseting the filter
	 *
	 * @return void
	 */
	public function handleResetFilter(): void
	{
		/**
		 * Session stuff
		 */
		$this->deleteSessionData('_grid_page');

		if ($this->default_filter_use_on_reset) {
			$this->deleteSessionData('_grid_has_filtered');
		}

		if ($this->default_sort_use_on_reset) {
			$this->deleteSessionData('_grid_has_sorted');
		}

		foreach ($this->getSessionData() as $key => $value) {
			if (!in_array($key, [
				'_grid_per_page',
				'_grid_sort',
				'_grid_page',
				'_grid_has_filtered',
				'_grid_has_sorted',
				'_grid_hidden_columns',
				'_grid_hidden_columns_manipulated',
			], true)) {
				$this->deleteSessionData($key);
			}
		}

		$this->filter = [];

		$this->reloadTheWholeGrid();
	}


	/**
	 * @param  string $key
	 * @return void
	 */
	public function handleResetColumnFilter(string $key): void
	{
		$this->deleteSessionData($key);
		unset($this->filter[$key]);

		$this->reloadTheWholeGrid();
	}


	/**
	 * @param bool $reset
	 * @return static
	 */
	public function setColumnReset(bool $reset = true)
	{
		$this->has_column_reset = (bool) $reset;

		return $this;
	}


	/**
	 * @return bool
	 */
	public function hasColumnReset(): bool
	{
		return $this->has_column_reset;
	}


	/**
	 * @param  Filter\Filter[] $filters
	 * @return void
	 */
	public function sendNonEmptyFiltersInPayload($filters): void
	{
		if (!$this->hasColumnReset()) {
			return;
		}

		$non_empty_filters = [];

		foreach ($filters as $filter) {
			if ($filter->isValueSet()) {
				$non_empty_filters[] = $filter->getKey();
			}
		}

		$this->getPresenter()->payload->non_empty_filters = $non_empty_filters;
	}


	/**
	 * Handler for export
	 *
	 * @param  int $id Key for particular export class in array $this->exports
	 * @return void
	 */
	public function handleExport(int $id): void
	{
		if (!isset($this->exports[$id])) {
			throw new Nette\Application\ForbiddenRequestException();
		}

		if (!empty($this->columns_export_order)) {
			$this->setColumnsOrder($this->columns_export_order);
		}

		$export = $this->exports[$id];

		/**
		 * Invoke possible events
		 */
		$this->onExport($this);

		if ($export->isFiltered()) {
			$sort = $this->sort;
			$filter = $this->assembleFilters();
		} else {
			$sort = [$this->primary_key => 'ASC'];
			$filter = [];
		}

		if ($this->dataModel === null) {
			throw new DataGridException('You have to set a data source first.');
		}

		$rows = [];

		$items = Nette\Utils\Callback::invokeArgs(
			[$this->dataModel, 'filterData'],
			[
				null,
				$this->createSorting($this->sort, $this->sort_callback),
				$filter,
			]
		);

		foreach ($items as $item) {
			$rows[] = new Row($this, $item, $this->getPrimaryKey());
		}

		if ($export instanceof Export\ExportCsv) {
			$export->invoke($rows);
		} else {
			$export->invoke($items);
		}

		if ($export->isAjax()) {
			$this->reload();
		}
	}


	/**
	 * Handler for getting children of parent item (e.g. category)
	 *
	 * @param  int $parent
	 * @return void
	 */
	public function handleGetChildren(int $parent): void
	{
		$this->setDataSource(
			call_user_func($this->tree_view_children_callback, $parent)
		);

		if ($this->getPresenter()->isAjax()) {
			$this->getPresenter()->payload->_datagrid_url = $this->refresh_url;
			$this->getPresenter()->payload->_datagrid_tree = $parent;

			$this->redrawControl('items');

			$this->onRedraw();
		} else {
			$this->getPresenter()->redirect('this');
		}
	}


	/**
	 * Handler for getting item detail
	 *
	 * @param  mixed $id
	 * @return void
	 */
	public function handleGetItemDetail($id): void
	{
		$this->getTemplate()->add('toggle_detail', $id);
		$this->redraw_item = [$this->items_detail->getPrimaryWhereColumn() => $id];

		if ($this->getPresenter()->isAjax()) {
			$this->getPresenter()->payload->_datagrid_toggle_detail = $id;
			$this->getPresenter()->payload->_datagrid_name = $this->getName();
			$this->redrawControl('items');

			/**
			 * Only for nette 2.4
			 */
			if (method_exists($this->getTemplate()->getLatte(), 'addProvider')) {
				$this->redrawControl('gridSnippets');
			}

			$this->onRedraw();
		} else {
			$this->getPresenter()->redirect('this');
		}
	}


	/**
	 * Handler for inline editing
	 *
	 * @param  mixed $id
	 * @param  mixed $key
	 * @return void
	 */
	public function handleEdit($id, $key): void
	{
		$column = $this->getColumn($key);
		$value = $this->getPresenter()->getRequest()->getPost('value');

		/**
		 * @var mixed Could be NULL of course
		 */
		$new_value = call_user_func_array($column->getEditableCallback(), [$id, $value]);

		$this->getPresenter()->payload->_datagrid_editable_new_value = $new_value;
	}


	/**
	 * Redraw $this
	 *
	 * @return void
	 */
	public function reload($snippets = []): void
	{
		if ($this->getPresenter()->isAjax()) {
			$this->redrawControl('tbody');
			$this->redrawControl('pagination');
			$this->redrawControl('summary');
			$this->redrawControl('thead-group-action');

			/**
			 * manualy reset exports links...
			 */
			$this->resetExportsLinks();
			$this->redrawControl('exports');

			foreach ($snippets as $snippet) {
				$this->redrawControl($snippet);
			}

			$this->getPresenter()->payload->_datagrid_url = $this->refresh_url;
			$this->getPresenter()->payload->_datagrid_name = $this->getName();

			$this->onRedraw();
		} else {
			$this->getPresenter()->redirect('this');
		}
	}


	/**
	 * @return void
	 */
	public function reloadTheWholeGrid(): void
	{
		if ($this->getPresenter()->isAjax()) {
			$this->redrawControl('grid');

			$this->getPresenter()->payload->_datagrid_url = $this->refresh_url;
			$this->getPresenter()->payload->_datagrid_name = $this->getName();

			$this->onRedraw();
		} else {
			$this->getPresenter()->redirect('this');
		}
	}


	/**
	 * Handler for column status
	 *
	 * @param  string $id
	 * @param  string $key
	 * @param  string $value
	 * @return void
	 */
	public function handleChangeStatus(string $id, string $key, string $value): void
	{
		if (empty($this->columns[$key])) {
			throw new DataGridException("ColumnStatus[$key] does not exist");
		}

		$this->columns[$key]->onChange($id, $value);
	}


	/**
	 * Redraw just one row via ajax
	 *
	 * @param  int   $id
	 * @param  mixed $primary_where_column
	 * @return void
	 */
	public function redrawItem(int $id, $primary_where_column = null): void
	{
		$this->snippets_set = true;

		$this->redraw_item = [($primary_where_column ?: $this->primary_key) => $id];

		$this->redrawControl('items');

		$this->getPresenter()->payload->_datagrid_url = $this->refresh_url;

		$this->onRedraw();
	}


	/**
	 * Tell datagrid to display all columns
	 *
	 * @return void
	 */
	public function handleShowAllColumns(): void
	{
		$this->deleteSessionData('_grid_hidden_columns');
		$this->saveSessionData('_grid_hidden_columns_manipulated', true);

		$this->redrawControl();

		$this->onRedraw();
	}


	/**
	 * Tell datagrid to display default columns
	 *
	 * @return void
	 */
	public function handleShowDefaultColumns(): void
	{
		$this->deleteSessionData('_grid_hidden_columns');
		$this->saveSessionData('_grid_hidden_columns_manipulated', false);

		$this->redrawControl();

		$this->onRedraw();
	}


	/**
	 * Reveal particular column
	 *
	 * @param  string $column
	 * @return void
	 */
	public function handleShowColumn(string $column): void
	{
		$columns = $this->getSessionData('_grid_hidden_columns');

		if (!empty($columns)) {
			$pos = array_search($column, $columns, true);

			if ($pos !== false) {
				unset($columns[$pos]);
			}
		}

		$this->saveSessionData('_grid_hidden_columns', $columns);
		$this->saveSessionData('_grid_hidden_columns_manipulated', true);

		$this->redrawControl();

		$this->onRedraw();
	}


	/**
	 * Notice datagrid to not display particular columns
	 *
	 * @param  string $column
	 * @return void
	 */
	public function handleHideColumn(string $column): void
	{
		/**
		 * Store info about hiding a column to session
		 */
		$columns = $this->getSessionData('_grid_hidden_columns');

		if (empty($columns)) {
			$columns = [$column];
		} elseif (!in_array($column, $columns, true)) {
			array_push($columns, $column);
		}

		$this->saveSessionData('_grid_hidden_columns', $columns);
		$this->saveSessionData('_grid_hidden_columns_manipulated', true);

		$this->redrawControl();

		$this->onRedraw();
	}


	public function handleActionCallback($__key, $__id): void
	{
		$action = $this->getAction($__key);

		if (!($action instanceof Column\ActionCallback)) {
			throw new DataGridException("Action [$__key] does not exist or is not an callback aciton.");
		}

		$action->onClick($__id);
	}


	/********************************************************************************
	 *                                  PAGINATION                                  *
	 ********************************************************************************/

/**
 * Set options of select "items_per_page"
 *
 * @param array $items_per_page_list
 * @return static
 */
	public function setItemsPerPageList(array $items_per_page_list, $include_all = true)
	{
		$this->items_per_page_list = $items_per_page_list;

		if ($include_all) {
			$this->items_per_page_list[] = 'all';
		}

		return $this;
	}


	/**
	 * Set default "items per page" value in pagination select
	 *
	 * @param $count
	 * @return static
	 */
	public function setDefaultPerPage($count)
	{
		$this->default_per_page = $count;

		return $this;
	}


	/**
	 * User may set default "items per page" value, apply it
	 *
	 * @return void
	 */
	public function findDefaultPerPage(): void
	{
		if (!empty($this->per_page)) {
			return;
		}

		if (!empty($this->default_per_page)) {
			$this->per_page = $this->default_per_page;
		}

		$this->saveSessionData('_grid_per_page', $this->per_page);
	}


	/**
	 * Paginator factory
	 *
	 * @return Components\DataGridPaginator\DataGridPaginator
	 */
	public function createComponentPaginator(): Components\DataGridPaginator\DataGridPaginator
	{
		/**
		 * Init paginator
		 */
		$component = new Components\DataGridPaginator\DataGridPaginator(
			$this->getTranslator(),
			static::$icon_prefix
		);
		$paginator = $component->getPaginator();

		$paginator->setPage($this->page);
		$paginator->setItemsPerPage($this->getPerPage());

		if ($this->custom_paginator_template) {
			$component->setTemplateFile($this->custom_paginator_template);
		}

		return $component;
	}


	/**
	 * Get parameter per_page
	 *
	 * @return int
	 */
	public function getPerPage(): int
	{
		$items_per_page_list = $this->getItemsPerPageList();

		$per_page = $this->per_page ?: reset($items_per_page_list);

		if (($per_page !== 'all' && !in_array((int) $this->per_page, $items_per_page_list, true))
			|| ($per_page === 'all' && !in_array($this->per_page, $items_per_page_list, true))) {
			$per_page = reset($items_per_page_list);
		}

		return $per_page;
	}


	/**
	 * Get associative array of items_per_page_list
	 *
	 * @return array
	 */
	public function getItemsPerPageList(): array
	{
		if (empty($this->items_per_page_list)) {
			$this->setItemsPerPageList([10, 20, 50], true);
		}

		$list = array_flip($this->items_per_page_list);

		foreach ($list as $key => $value) {
			$list[$key] = $key;
		}

		if (array_key_exists('all', $list)) {
			$list['all'] = $this->getTranslator()->translate('ublaboo_datagrid.all');
		}

		return $list;
	}


	/**
	 * Order Grid to "be paginated"
	 *
	 * @param bool $do
	 * @return static
	 */
	public function setPagination(bool $do)
	{
		$this->do_paginate = (bool) $do;

		return $this;
	}


	/**
	 * Tell whether Grid is paginated
	 *
	 * @return bool
	 */
	public function isPaginated(): bool
	{
		return $this->do_paginate;
	}


	/**
	 * Return current paginator class
	 *
	 * @return NULL|Components\DataGridPaginator\DataGridPaginator
	 */
	public function getPaginator(): ?Components\DataGridPaginator\DataGridPaginator
	{
		if ($this->isPaginated() && $this->getPerPage() !== 'all') {
			return $this['paginator'];
		}

		return null;
	}


	/********************************************************************************
	 *                                     I18N                                     *
	 ********************************************************************************/

/**
 * Set datagrid translator
 *
 * @param Nette\Localization\ITranslator $translator
 * @return static
 */
	public function setTranslator(Nette\Localization\ITranslator $translator)
	{
		$this->translator = $translator;

		return $this;
	}


	/**
	 * Get translator for datagrid
	 *
	 * @return Nette\Localization\ITranslator
	 */
	public function getTranslator(): Nette\Localization\ITranslator
	{
		if (!$this->translator) {
			$this->translator = new Localization\SimpleTranslator();
		}

		return $this->translator;
	}


	/********************************************************************************
	 *                                 COLUMNS ORDER                                *
	 ********************************************************************************/

/**
 * Set order of datagrid columns
 *
 * @param array $order
 * @return static
 */
	public function setColumnsOrder(array $order)
	{
		$new_order = [];

		foreach ($order as $key) {
			if (isset($this->columns[$key])) {
				$new_order[$key] = $this->columns[$key];
			}
		}

		if (sizeof($new_order) === sizeof($this->columns)) {
			$this->columns = $new_order;
		} else {
			throw new DataGridException('When changing columns order, you have to specify all columns');
		}

		return $this;
	}


	/**
	 * Columns order may be different for export and normal grid
	 *
	 * @param array $order
	 */
	public function setColumnsExportOrder(array $order): void
	{
		$this->columns_export_order = (array) $order;
	}


	/********************************************************************************
	 *                                SESSION & URL                                 *
	 ********************************************************************************/

/**
 * Find some unique session key name
 *
 * @return string
 */
	public function getSessionSectionName(): string
	{
		return $this->getPresenter()->getName() . ':' . $this->getUniqueId();
	}


	/**
	 * Should datagrid remember its filters/pagination/etc using session?
	 *
	 * @param bool $remember
	 * @return static
	 */
	public function setRememberState(bool $remember = true)
	{
		$this->remember_state = (bool) $remember;

		return $this;
	}


	/**
	 * Should datagrid refresh url using history API?
	 *
	 * @param bool $refresh
	 * @return static
	 */
	public function setRefreshUrl(bool $refresh = true)
	{
		$this->refresh_url = (bool) $refresh;

		return $this;
	}


	/**
	 * Get session data if functionality is enabled
	 *
	 * @param  string $key
	 * @return mixed
	 */
	public function getSessionData(?string $key = null, $default_value = null)
	{
		if (!$this->remember_state) {
			return $key ? $default_value : [];
		}

		return ($key ? $this->grid_session->{$key} : $this->grid_session) ?: $default_value;
	}


	/**
	 * Save session data - just if it is enabled
	 *
	 * @param  string $key
	 * @param  mixed  $value
	 * @return void
	 */
	public function saveSessionData(string $key, $value): void
	{
		if ($this->remember_state) {
			$this->grid_session->{$key} = $value;
		}
	}


	/**
	 * Delete session data
	 *
	 * @return void
	 */
	public function deleteSessionData($key): void
	{
		unset($this->grid_session->{$key});
	}


	/**
	 * Delete session data
	 *
	 * @return void
	 * @deprecated
	 */
	public function deleteSesssionData($key): void
	{
		@trigger_error('deleteSesssionData is deprecated, use deleteSessionData instead', E_USER_DEPRECATED);
		$this->deleteSessionData($key);
	}


	/********************************************************************************
	 *                                  ITEM DETAIL                                 *
	 ********************************************************************************/

/**
 * Get items detail parameters
 *
 * @return array
 */
	public function getItemsDetail(): array
	{
		return $this->items_detail;
	}


	/**
	 * Items can have thair detail - toggled
	 *
	 * @param mixed $detail callable|string|bool
	 * @param string|null $primary_where_column
	 * @return Column\ItemDetail
	 */
	public function setItemsDetail($detail = true, $primary_where_column = null): Column\ItemDetail
	{
		if ($this->isSortable()) {
			throw new DataGridException('You can not use both sortable datagrid and items detail.');
		}

		$this->items_detail = new Column\ItemDetail(
			$this,
			$primary_where_column ?: $this->primary_key
		);

		if (is_string($detail)) {
			/**
			 * Item detail will be in separate template
			 */
			$this->items_detail->setType('template');
			$this->items_detail->setTemplate($detail);

		} elseif (is_callable($detail)) {
			/**
			 * Item detail will be rendered via custom callback renderer
			 */
			$this->items_detail->setType('renderer');
			$this->items_detail->setRenderer($detail);

		} elseif ($detail === true) {
			/**
			 * Item detail will be rendered probably via block #detail
			 */
			$this->items_detail->setType('block');

		} else {
			throw new DataGridException(
				'::setItemsDetail() can be called either with no parameters or with parameter = template path or callable renderer.'
			);
		}

		return $this->items_detail;
	}


	/**
	 * @param callable $callable_set_container
	 * @return static
	 */
	public function setItemsDetailForm(callable $callable_set_container)
	{
		if ($this->items_detail instanceof Column\ItemDetail) {
			$this->items_detail->setForm(
				new Utils\ItemDetailForm($callable_set_container)
			);

			return $this;
		}

		throw new DataGridException('Please set the ItemDetail first.');
	}


	/**
	 * @return Nette\Forms\Container|NULL
	 */
	public function getItemDetailForm(): ?Nette\Forms\Container
	{
		if ($this->items_detail instanceof Column\ItemDetail) {
			return $this->items_detail->getForm();
		}

		return null;
	}


	/********************************************************************************
	 *                                ROW PRIVILEGES                                *
	 ********************************************************************************/

/**
 * @param  callable $condition
 * @return void
 */
	public function allowRowsGroupAction(callable $condition): void
	{
		$this->row_conditions['group_action'] = $condition;
	}


	/**
	 * @param  callable $condition
	 * @return void
	 */
	public function allowRowsInlineEdit(callable $condition): void
	{
		$this->row_conditions['inline_edit'] = $condition;
	}


	/**
	 * @param  string   $key
	 * @param  callable $condition
	 * @return void
	 */
	public function allowRowsAction(string $key, callable $condition): void
	{
		$this->row_conditions['action'][$key] = $condition;
	}


	/**
	 * @param  string   $multiActionKey
	 * @param  string   $actionKey
	 * @param  callable $condition
	 * @return void
	 */
	public function allowRowsMultiAction(string $multiActionKey, string $actionKey, callable $condition): void
	{
		if (!isset($this->actions[$multiActionKey])) {
			throw new DataGridException("There is no action at key [$multiActionKey] defined.");
		}

		if (!$this->actions[$multiActionKey] instanceof Column\MultiAction) {
			throw new DataGridException("Action at key [$multiActionKey] is not a MultiAction.");
		}

		$this->actions[$multiActionKey]->setRowCondition((string) $actionKey, $condition);
	}


	/**
	 * @param  string      $name
	 * @param  string|null $key
	 * @return bool|callable
	 */
	public function getRowCondition(string $name, ?string $key = null)
	{
		if (!isset($this->row_conditions[$name])) {
			return false;
		}

		$condition = $this->row_conditions[$name];

		if (!$key) {
			return $condition;
		}

		return $condition[$key] ?? false;
	}


	/********************************************************************************
	 *                               COLUMN CALLBACK                                *
	 ********************************************************************************/

/**
 * @param  string   $key
 * @param  callable $callback
 * @return void
 */
	public function addColumnCallback(string $key, callable $callback): void
	{
		$this->column_callbacks[$key] = $callback;
	}


	/**
	 * @param  string $key
	 * @return callable|null
	 */
	public function getColumnCallback(string $key): ?callable
	{
		return empty($this->column_callbacks[$key]) ? null : $this->column_callbacks[$key];
	}


	/********************************************************************************
	 *                                 INLINE EDIT                                  *
	 ********************************************************************************/

/**
 * @return InlineEdit
 */
	public function addInlineEdit($primary_where_column = null): InlineEdit
	{
		$this->inlineEdit = new InlineEdit($this, $primary_where_column ?: $this->primary_key);

		return $this->inlineEdit;
	}


	/**
	 * @return InlineEdit|null
	 */
	public function getInlineEdit(): ?InlineEdit
	{
		return $this->inlineEdit;
	}


	/**
	 * @param  mixed $id
	 * @return void
	 */
	public function handleInlineEdit($id): void
	{
		if ($this->inlineEdit) {
			$this->inlineEdit->setItemId($id);

			$primary_where_column = $this->inlineEdit->getPrimaryWhereColumn();

			$this['filter']['inline_edit']->addHidden('_id', $id);
			$this['filter']['inline_edit']->addHidden('_primary_where_column', $primary_where_column);

			if ($this->getPresenter()->isAjax()) {
				$this->getPresenter()->payload->_datagrid_inline_editing = true;
				$this->getPresenter()->payload->_datagrid_name = $this->getName();
			}

			$this->redrawItem($id, $primary_where_column);
		}
	}


	/********************************************************************************
	 *                                  INLINE ADD                                  *
	 ********************************************************************************/

/**
 * @return InlineEdit
 */
	public function addInlineAdd(): InlineEdit
	{
		$this->inlineAdd = new InlineEdit($this);

		$this->inlineAdd
			->setTitle('ublaboo_datagrid.add')
			->setIcon('plus')
			->setClass('btn btn-xs btn-default btn-secondary');

		return $this->inlineAdd;
	}


	/**
	 * @return InlineEdit|null
	 */
	public function getInlineAdd(): ?InlineEdit
	{
		return $this->inlineAdd;
	}


	/********************************************************************************
	 *                               HIDEABLE COLUMNS                               *
	 ********************************************************************************/

/**
 * Can datagrid hide colums?
 *
 * @return bool
 */
	public function canHideColumns(): bool
	{
		return (bool) $this->can_hide_columns;
	}


	/**
	 * Order Grid to set columns hideable.
	 *
	 * @return static
	 */
	public function setColumnsHideable()
	{
		$this->can_hide_columns = true;

		return $this;
	}


	/********************************************************************************
	 *                                COLUMNS SUMMARY                               *
	 ********************************************************************************/

/**
 * Will datagrid show summary in the end?
 *
 * @return bool
 */
	public function hasColumnsSummary(): bool
	{
		return $this->columnsSummary instanceof ColumnsSummary;
	}


	/**
	 * Set columns to be summarized in the end.
	 *
	 * @param array    $columns
	 * @param callable $rowCallback
	 * @return \Ublaboo\DataGrid\ColumnsSummary
	 */
	public function setColumnsSummary(array $columns, ?callable $rowCallback = null): ColumnsSummary
	{
		if ($this->hasSomeAggregationFunction()) {
			throw new DataGridException('You can use either ColumnsSummary or AggregationFunctions');
		}

		if (!empty($rowCallback)) {
			if (!is_callable($rowCallback)) {
				throw new \InvalidArgumentException('Row summary callback must be callable');
			}
		}

		$this->columnsSummary = new ColumnsSummary($this, $columns, $rowCallback);

		return $this->columnsSummary;
	}


	/**
	 * @return ColumnsSummary|NULL
	 */
	public function getColumnsSummary(): ?ColumnsSummary
	{
		return $this->columnsSummary;
	}


	/********************************************************************************
	 *                                   INTERNAL                                   *
	 ********************************************************************************/

/**
 * Tell grid filters to by submitted automatically
 *
 * @param bool $auto
 */
	public function setAutoSubmit(bool $auto = true)
	{
		$this->auto_submit = (bool) $auto;

		return $this;
	}


	/**
	 * @return bool
	 */
	public function hasAutoSubmit(): bool
	{
		return $this->auto_submit;
	}


	/**
	 * Submit button when no auto-submitting is used
	 *
	 * @return Filter\SubmitButton
	 */
	public function getFilterSubmitButton(): Filter\SubmitButton
	{
		if ($this->hasAutoSubmit()) {
			throw new DataGridException(
				'DataGrid has auto-submit. Turn it off before setting filter submit button.'
			);
		}

		if ($this->filter_submit_button === null) {
			$this->filter_submit_button = new Filter\SubmitButton($this);
		}

		return $this->filter_submit_button;
	}


	/********************************************************************************
	 *                                   INTERNAL                                   *
	 ********************************************************************************/

/**
 * Get count of columns
 *
 * @return int
 */
	public function getColumnsCount(): int
	{
		$count = sizeof($this->getColumns());

		if (!empty($this->actions)
			|| $this->isSortable()
			|| $this->getItemsDetail()
			|| $this->getInlineEdit()
			|| $this->getInlineAdd()) {
			$count++;
		}

		if ($this->hasGroupActions()) {
			$count++;
		}

		return $count;
	}


	/**
	 * Get primary key of datagrid data source
	 *
	 * @return string
	 */
	public function getPrimaryKey(): string
	{
		return $this->primary_key;
	}


	/**
	 * Get set of set columns
	 * @return Column\Column[]
	 */
	public function getColumns()
	{
		$return = $this->columns;

		try {
			$this->getParent();

			if (!$this->getSessionData('_grid_hidden_columns_manipulated', false)) {
				$columns_to_hide = [];

				foreach ($this->columns as $key => $column) {
					if ($column->getDefaultHide()) {
						$columns_to_hide[] = $key;
					}
				}

				if (!empty($columns_to_hide)) {
					$this->saveSessionData('_grid_hidden_columns', $columns_to_hide);
					$this->saveSessionData('_grid_hidden_columns_manipulated', true);
				}
			}

			$hidden_columns = $this->getSessionData('_grid_hidden_columns', []);

			foreach ($hidden_columns as $column) {
				if (!empty($this->columns[$column])) {
					$this->columns_visibility[$column] = [
						'visible' => false,
					];

					unset($return[$column]);
				}
			}

		} catch (DataGridHasToBeAttachedToPresenterComponentException $e) {
		}

		return $return;
	}


	public function getColumnsVisibility()
	{
		$return = $this->columns_visibility;

		foreach ($this->columns_visibility as $key => $column) {
			$return[$key]['column'] = $this->columns[$key];
		}

		return $return;
	}


	/**
	 * @return PresenterComponent
	 */
	public function getParent(): PresenterComponent
	{
		$parent = parent::getParent();

		if (!($parent instanceof PresenterComponent)) {
			throw new DataGridHasToBeAttachedToPresenterComponentException(
                "DataGrid is attached to: '" . ($parent ? get_class($parent) : 'null') . "', but instance of PresenterComponent is needed."
			);
		}

		return $parent;
	}


	/**
	 * @return string
	 */
	public function getSortableParentPath(): string
	{
		return $this->getParent()->lookupPath(Nette\Application\IPresenter::class, false);
	}


	/**
	 * Some of datagrid columns is hidden by default
	 *
	 * @param bool $default_hide
	 */
	public function setSomeColumnDefaultHide(bool $default_hide): void
	{
		$this->some_column_default_hide = $default_hide;
	}


	/**
	 * Are some of columns hidden bydefault?
	 */
	public function hasSomeColumnDefaultHide()
	{
		return $this->some_column_default_hide;
	}


	/**
	 * Simply refresh url
	 *
	 * @return void
	 */
	public function handleRefreshState(): void
	{
		$this->findSessionValues();
		$this->findDefaultFilter();
		$this->findDefaultSort();
		$this->findDefaultPerPage();

		$this->getPresenter()->payload->_datagrid_url = $this->refresh_url;
		$this->redrawControl('non-existing-snippet');
	}


	/**
	 * @param string $template_file
	 * @return void
	 */
	public function setCustomPaginatortemplate($template_file)
	{
		$this->custom_paginator_template = $template_file;
	}

}
