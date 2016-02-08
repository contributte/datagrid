<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid;

use Nette;
use Ublaboo\DataGrid\Utils\ArraysHelper;
use Nette\Application\UI\Form;

class DataGrid extends Nette\Application\UI\Control
{

	/**
	 * @var callable[]
	 */
	public $onRedraw;

	/**
	 * @var string
	 * @todo Tell about this on github
	 */
	public static $icon_prefix = 'fa fa-';

	/**
	 * When set to TRUE, datagrid throws an exception
	 * 	when tring to get related entity within join and entity does not exist
	 * @var bool
	 */
	public $strict_entity_property = FALSE;

	/**
	 * @var int
	 * @persistent
	 */
	public $page = 1;

	/**
	 * @var int
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
	 * @persistent
	 */
	public $filter = [];

	/**
	 * @var Callable[]
	 */
	public $onRender = [];

	/**
	 * @var array
	 */
	protected $items_per_page_list = [10, 20, 50];

	/**
	 * @var string
	 */
	protected $template_file;

	/**
	 * @var Column\IColumn[]
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
	protected $do_paginate = TRUE;

	/**
	 * @var bool
	 */
	protected $csv_export = TRUE;

	/**
	 * @var bool
	 */
	protected $csv_export_filtered = TRUE;

	/**
	 * @var bool
	 */
	protected $sortable = FALSE;
	
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
	 * @var string
	 */
	protected $tree_view_has_children_column;

	/**
	 * @var bool
	 */
	protected $outer_filter_rendering = FALSE;

	/**
	 * @var array
	 */
	protected $columns_export_order = [];

	/**
	 * @var bool
	 */
	private $remember_state = TRUE;

	/**
	 * @var bool
	 */
	private $refresh_url = TRUE;

	/**
	 * @var Nette\Http\SessionSection
	 */
	private $grid_session;

	/**
	 * @var array
	 */
	private $items_detail = [];

	/**
	 * @var array
	 */
	private $row_conditions = [
		'group_action' => FALSE,
		'action' => []
	];


	/**
	 * @param Nette\ComponentModel\IContainer|NULL $parent
	 * @param string                               $name
	 */
	public function __construct(Nette\ComponentModel\IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);

		$this->monitor('Nette\Application\UI\Presenter');
	}


	/**
	 * {inheritDoc}
	 * @return void
	 */
	public function attached($presenter)
	{
		parent::attached($presenter);

		if ($presenter instanceof Nette\Application\UI\Presenter) {
			/**
			 * Get session
			 */
			$this->grid_session = $this->getPresenter()->getSession($this->getSessionSectionName());

			/**
			 * Try to find previous filters/pagination/sort in session
			 */
			$this->findSessionFilters();
		}
	}


	/**
	 * Find some unique session key name
	 * @return string
	 */
	public function getSessionSectionName()
	{
		return $this->getPresenter()->getName().':'.$this->getName();
	}


	/**
	 * Render template
	 * @return void
	 */
	public function render()
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

		$this->template->setTranslator($this->getTranslator());

		/**
		 * Invoke some possible events
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
					$this->sort,
					$this->assableFilters()
				]
			);
		}

		foreach ($items as $item) {
			$rows[] = new Row($this, $item, $this->getPrimaryKey());
		}

		if ($this->isTreeView()) {
			$this->template->tree_view_has_children_column = $this->tree_view_has_children_column;
		}

		$this->template->rows = $rows;

		$this->template->columns = $this->columns;
		$this->template->actions = $this->actions;
		$this->template->exports = $this->exports;
		$this->template->filters = $this->filters;

		$this->template->filter_active = $this->isFilterActive();
		$this->template->original_template = $this->getOriginalTemplateFile();
		$this->template->icon_prefix = static::$icon_prefix;
		$this->template->items_detail = $this->items_detail;

		/**
		 * Walkaround for Latte (does not know $form in snippet in {form} etc)
		 */
		$this->template->filter = $this['filter'];

		/**
		 * Set template file and render it
		 */
		$this->template->setFile($this->getTemplateFile())->render();
	}


	/**
	 * Return current paginator class
	 * @return NULL|Components\DataGridPaginator\DataGridPaginator
	 */
	public function getPaginator()
	{
		if ($this->isPaginated() && $this->per_page !== 'all') {
			return $this['paginator'];
		}

		return NULL;
	}


	/**
	 * @param string $primary_key
	 */
	public function setPrimaryKey($primary_key)
	{
		$this->primary_key = $primary_key;

		return $this;
	}


	/**
	 * Set Grid data source
	 * @param DataSource\IDataSource|array|\DibiFluent $source
	 * @return DataGrid
	 */
	public function setDataSource($source)
	{
		if ($source instanceof DataSource\IDataSource) {
			// $source is ready for interact

		} else if (is_array($source)) {
			$data_source = new DataSource\ArrayDataSource($source);

		} else if ($source instanceof \DibiFluent) {
			$driver = $source->getConnection()->getDriver();

			if ($driver instanceof \DibiOdbcDriver) {
				$data_source = new DataSource\DibiFluentMssqlDataSource($source, $this->primary_key);

			} else if ($driver instanceof \DibiMsSqlDriver) {
				$data_source = new DataSource\DibiFluentMssqlDataSource($source, $this->primary_key);

			} else {
				$data_source = new DataSource\DibiFluentDataSource($source, $this->primary_key);
			}

		} else if ($source instanceof Nette\Database\Table\Selection) {
			$data_source = new DataSource\NetteDatabaseTableDataSource($source, $this->primary_key);

		} else if ($source instanceof \Kdyby\Doctrine\QueryBuilder) {
			$data_source = new DataSource\DoctrineDataSource($source, $this->primary_key);

		} else {
			$data_source_class = $source ? get_class($source) : 'NULL';
			throw new DataGridException("DataGrid can not take [$data_source_class] as data source.");
		}

		$this->dataModel = new DataModel($data_source);

		return $this;
	}


	/**
	 * Is filter active?
	 * @return boolean
	 */
	public function isFilterActive()
	{
		$is_filter = ArraysHelper::testTruthy($this->filter);

		return ($is_filter) || $this->force_filter_active;
	}


	/**
	 * Tell that filter is active from whatever reasons
	 * return self
	 */
	public function setFilterActive()
	{
		$this->force_filter_active = TRUE;

		return $this;
	}


	/**
	 * If we want to sent some initial filter
	 * @param array $filter
	 */
	public function setFilter(array $filter)
	{
		$this->filter = $filter;

		return $this;
	}


	/**
	 * Set options of select "items_per_page"
	 * @param array $items_per_page_list
	 */
	public function setItemsPerPageList(array $items_per_page_list)
	{
		$this->items_per_page_list = $items_per_page_list;

		return $this;
	}


	/**
	 * Set custom template file to render
	 * @param string $template_file
	 */
	public function setTemplateFile($template_file)
	{
		$this->template_file = $template_file;

		return $this;
	}


	/**
	 * Get DataGrid template file
	 * @return string
	 */
	public function getTemplateFile()
	{
		return $this->template_file ?: $this->getOriginalTemplateFile();
	}


	/**
	 * Get DataGrid original template file
	 * @return string
	 */
	public function getOriginalTemplateFile()
	{
		return __DIR__.'/templates/datagrid.latte';
	}


	/**
	 * Order Grid to "be paginated"
	 * @param bool $do
	 */
	public function setPagination($do)
	{
		$this->do_paginate = (bool) $do;

		return $this;
	}


	/**
	 * Tell whether Grid is paginated
	 * @return bool
	 */
	public function isPaginated()
	{
		return $this->do_paginate;
	}


	/**
	 * Set grido to be sortable
	 * @param bool $sortable
	 */
	public function setSortable($sortable = TRUE)
	{
		if ($this->getItemsDetail()) {
			throw new DataGridException('You can not use both sortable datagrid and items detail.');
		}

		$this->sortable = (bool) $sortable;

		return $this;
	}


	/**
	 * Set sortable handle
	 * @param string $handle
	 */
	public function setSortableHandler($handler = 'sort!')
	{
		$this->sortable_handler = (string) $handler;

		return $this;
	}


	/**
	 * Tell whether DataGrid is sortable
	 * @return bool
	 */
	public function isSortable()
	{
		return $this->sortable;
	}

	/**
	 * Return sortable handle name
	 * @return string
	 */
	public function getSortableHandler()
	{
		return $this->sortable_handler;
	}


	/**
	 * Is tree view set?
	 * @return boolean
	 */
	public function isTreeView()
	{
		return (bool) $this->tree_view_children_callback;
	}


	/**
	 * Setting tree view
	 * @param callable $get_children_callback
	 * @param string   $tree_view_has_children_column
	 */
	public function setTreeView($get_children_callback, $tree_view_has_children_column = 'has_children')
	{
		if (!is_callable($get_children_callback)) {
			throw new DataGridException(
				'Parameters to method DataGrid::setTreeView must be of type callable'
			);
		}

		$this->tree_view_children_callback = $get_children_callback;
		$this->tree_view_has_children_column = $tree_view_has_children_column;

		/**
		 * TUrn off pagination
		 */
		$this->setPagination(NULL);

		/**
		 * Set tree view template file
		 */
		if (!$this->template_file) {
			$this->setTemplateFile(__DIR__.'/templates/datagrid_tree.latte');
		}

		return $this;
	}


	/********************************************************************************
	 *                                    Columns                                   *
	 ********************************************************************************/


	/**
	 * Add text column with no other formating
	 * @param  string      $key
	 * @param  string      $name
	 * @param  string|null $column
	 * @return Column\Column
	 */
	public function addColumnText($key, $name, $column = NULL)
	{
		$this->addColumnCheck($key);
		$column = $column ?: $key;

		return $this->columns[$key] = new Column\ColumnText($column, $name);
	}


	/**
	 * Add column with link
	 * @param  string      $key
	 * @param  string      $name
	 * @param  string|null $column
	 * @return Column\Column
	 */
	public function addColumnLink($key, $name, $href = NULL, $column = NULL, array $params = NULL)
	{
		$this->addColumnCheck($key);
		$column = $column ?: $key;
		$href = $href ?: $key;

		if (NULL === $params) {
			$params = [$this->primary_key];
		}

		return $this->columns[$key] = new Column\ColumnLink($this, $column, $name, $href, $params);
	}


	/**
	 * Add column with possible number formating
	 * @param  string      $key
	 * @param  string      $name
	 * @param  string|null $column
	 * @return Column\Column
	 */
	public function addColumnNumber($key, $name, $column = NULL)
	{
		$this->addColumnCheck($key);
		$column = $column ?: $key;

		return $this->columns[$key] = new Column\ColumnNumber($column, $name);
	}


	/**
	 * Add column with date formating
	 * @param  string      $key
	 * @param  string      $name
	 * @param  string|null $column
	 * @return Column\Column
	 */
	public function addColumnDateTime($key, $name, $column = NULL)
	{
		$this->addColumnCheck($key);
		$column = $column ?: $key;

		return $this->columns[$key] = new Column\ColumnDateTime($column, $name);
	}


	/**
	 * Return existing column
	 * @param  string $key
	 * @return Column\Column
	 * @throws DataGridException
	 */
	public function getColumn($key)
	{
		if (!isset($this->columns[$key])) {
			throw new DataGridException("There is no column at key [$key] defined.");
		}

		return $this->columns[$key];
	}


	/**
	 * Remove column
	 * @param string $key
	 * @return void
	 */
	public function removeColumn($key)
	{
		unset($this->columns[$key]);
	}


	/**
	 * Check whether given key already exists in $this->columns
	 * @param  string $key
	 * @throws DataGridException
	 */
	protected function addColumnCheck($key)
	{
		if (isset($this->columns[$key])) {
			throw new DataGridException("There is already column at key [$key] defined.");
		}
	}


	/********************************************************************************
	 *                                    Actions                                   *
	 ********************************************************************************/


	/**
	 * Create action
	 * @param string     $key
	 * @param string     $name
	 * @param string     $href
	 * @param array|null $params
	 */
	public function addAction($key, $name = '', $href = NULL, array $params = NULL)
	{
		$this->addActionCheck($key);
		$href = $href ?: $key;

		if (NULL === $params) {
			$params = [$this->primary_key];
		}

		return $this->actions[$key] = new Column\Action($this, $href, $name, $params);
	}


	/**
	 * Get existing action
	 * @param  string       $key
	 * @return Column\Action
	 * @throws DataGridException
	 */
	public function getAction($key)
	{
		if (!isset($this->actions[$key])) {
			throw new DataGridException("There is no action at key [$key] defined.");
		}

		return $this->actions[$key];
	}


	/**
	 * Remove action
	 * @param string $key
	 * @return void
	 */
	public function removeAction($key)
	{
		unset($this->actions[$key]);
	}


	/**
	 * Check whether given key already exists in $this->filters
	 * @param  string $key
	 * @throws DataGridException
	 */
	protected function addActionCheck($key)
	{
		if (isset($this->actions[$key])) {
			throw new DataGridException("There is already action at key [$key] defined.");
		}
	}


	/********************************************************************************
	 *                                    Filters                                   *
	 ********************************************************************************/


	/**
	 * Add filter fot text search
	 * @param string       $key
	 * @param string       $name
	 * @param array|string $columns
	 * @throws DataGridException
	 */
	public function addFilterText($key, $name, $columns = NULL)
	{
		$columns = NULL === $columns ? [$key] : (is_string($columns) ? [$columns] : $columns);

		if (!is_array($columns)) {
			throw new DataGridException("Filter Text can except only array or string.");
		}

		$this->addFilterCheck($key);

		return $this->filters[$key] = new Filter\FilterText($key, $name, $columns);
	}


	/**
	 * Add select box filter
	 * @param string $key
	 * @param string $name
	 * @param array  $options
	 * @param string $column
	 * @throws DataGridException
	 */
	public function addFilterSelect($key, $name, $options, $column = NULL)
	{
		$column = $column ?: $key;

		if (!is_string($column)) {
			throw new DataGridException("Filter Select can only filter through one column.");
		}

		$this->addFilterCheck($key);

		return $this->filters[$key] = new Filter\FilterSelect($key, $name, $options, $column);
	}


	/**
	 * Add datepicker filter
	 * @param string $key
	 * @param string $name
	 * @param string $column
	 * @throws DataGridException
	 */
	public function addFilterDate($key, $name, $column = NULL)
	{
		$column = $column ?: $key;

		if (!is_string($column)) {
			throw new DataGridException("FilterDate can only filter through one column.");
		}

		$this->addFilterCheck($key);

		return $this->filters[$key] = new Filter\FilterDate($key, $name, $column);
	}


	/**
	 * Add range filter (from - to)
	 * @param string $key
	 * @param string $name
	 * @param string $column
	 * @throws DataGridException
	 */
	public function addFilterRange($key, $name, $column = NULL, $name_second = '-')
	{
		$column = $column ?: $key;

		if (!is_string($column)) {
			throw new DataGridException("FilterRange can only filter through one column.");
		}

		$this->addFilterCheck($key);

		return $this->filters[$key] = new Filter\FilterRange($key, $name, $column, $name_second);
	}


	/**
	 * Add datepicker filter (from - to)
	 * @param string $key
	 * @param string $name
	 * @param string $column
	 * @throws DataGridException
	 */
	public function addFilterDateRange($key, $name, $column = NULL, $name_second = '-')
	{
		$column = $column ?: $key;

		if (!is_string($column)) {
			throw new DataGridException("FilterDateRange can only filter through one column.");
		}

		$this->addFilterCheck($key);

		return $this->filters[$key] = new Filter\FilterDateRange($key, $name, $column, $name_second);
	}


	/**
	 * Check whether given key already exists in $this->filters
	 * @param  string $key
	 * @throws DataGridException
	 */
	protected function addFilterCheck($key)
	{
		if (isset($this->filters[$key])) {
			throw new DataGridException("There is already action at key [$key] defined.");
		}
	}


	/**
	 * Fill array of Filter\Filter[] with values from $this->filter persistent parameter
	 * Fill array of Column\Column[] with values from $this->sort   persistent parameter
	 * @return Filter\Filter[] $this->filters === Filter\Filter[]
	 */
	public function assableFilters()
	{
		foreach ($this->filter as $key => $value) {
			if (!isset($this->filters[$key])) {
				$this->deleteSesssionData($key);

				continue;
			}

			if (is_array($value) || $value instanceof \Traversable) {
				if (!ArraysHelper::testEmpty($value)) {
					$this->filters[$key]->setValue($value);
				}
			} else {
				if ($value !== '' && $value !== NULL) {
					$this->filters[$key]->setValue($value);
				}
			}
		}

		foreach ($this->columns as $column) {
			if (isset($this->sort[$column->getColumnName()])) {
				$column->setSort($this->sort);
			}
		}

		return $this->filters;
	}


	/**
	 * Try to restore session stuff
	 * @return void
	 */
	public function findSessionFilters()
	{
		if ($this->filter || ($this->page != 1) || $this->sort || $this->per_page) {
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
			if (!in_array($key, ['_grid_per_page', '_grid_sort', '_grid_page'])) {
				$this->filter[$key] = $value;
			}
		}
	}


	/**
	 * Remove filter
	 * @param string $key
	 * @return void
	 */
	public function removeFilter($key)
	{
		unset($this->filters[$key]);
	}


	/********************************************************************************
	 *                                    Exports                                   *
	 ********************************************************************************/


	/**
	 * Add export of type callback
	 * @param string   $text
	 * @param callable $callback
	 * @param boolean  $filtered
	 */
	public function addExportCallback($text, $callback, $filtered = FALSE)
	{
		if (!is_callable($callback)) {
			throw new DataGridException("Second parameter of ExportCallback must be callable.");
		}

		return $this->addToExports(new Export\Export($text, $callback, $filtered));
	}


	/**
	 * Add already implemented csv export
	 * @param string $text
	 * @param string $csv_file_name
	 */
	public function addExportCsv($text, $csv_file_name)
	{
		return $this->addToExports(new Export\ExportCsv($text, $csv_file_name, FALSE));
	}


	/**
	 * Add already implemented csv export, but for filtered data
	 * @param string $text
	 * @param string $csv_file_name
	 */
	public function addExportCsvFiltered($text, $csv_file_name)
	{
		return $this->addToExports(new Export\ExportCsv($text, $csv_file_name, TRUE));
	}


	/**
	 * Add export to array
	 * @param Export\Export $export
	 */
	protected function addToExports(Export\Export $export)
	{
		$id = ($s = sizeof($this->exports)) ? ($s + 1) : 1;

		$export->setLink($this->link('export!', ['id' => $id]));

		return $this->exports[$id] = $export;
	}


	/********************************************************************************
	 *                                 Group actions                                *
	 ********************************************************************************/


	/**
	 * Add group actino
	 * @param string $title
	 * @param array  $options
	 */
	public function addGroupAction($title, $options = [])
	{
		return $this->getGroupActionCollection()->addGroupAction($title, $options);
	}


	/**
	 * Get collection of all group actions
	 * @return GroupAction\GroupActionCollection
	 */
	public function getGroupActionCollection()
	{
		if (!$this->group_action_collection) {
			$this->group_action_collection = new GroupAction\GroupActionCollection();
		}

		return $this->group_action_collection;
	}


	/********************************************************************************
	 *                                    Signals                                   *
	 ********************************************************************************/


	/**
	 * Handler for changind page (just refresh site with page as persistent paramter set)
	 * @param  int  $page
	 * @return void
	 */
	public function handlePage($page)
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
	 * @return void
	 */
	public function handleSort(array $sort)
	{
		/**
		 * Session stuff
		 */
		$this->sort = $sort;
		$this->saveSessionData('_grid_sort', $this->sort);

		$this->reload(['table']);
	}


	/**
	 * handler for reseting the filter
	 * @return void
	 */
	public function handleResetFilter()
	{
		/**
		 * Session stuff
		 */
		$this->deleteSesssionData('_grid_page');

		foreach ($this->getSessionData() as $key => $value) {
			if (!in_array($key, ['_grid_per_page', '_grid_sort', '_grid_page'])) {
				$this->deleteSesssionData($key);
			}
		}

		$this->filter = [];

		$this->reload(['grid']);
	}


	/**
	 * Handler for export
	 * @param  int $id Key for particular export class in array $this->exports
	 * @return void
	 */
	public function handleExport($id)
	{
		if (!isset($this->exports[$id])) {
			throw new Nette\Application\ForbiddenRequestException;
		}

		if ($this->columns_export_order) {
			$this->setColumnsOrder($this->columns_export_order);
		}

		$export = $this->exports[$id];

		if ($export->isFiltered()) {
			$sort      = $this->sort;
			$filter    = $this->assableFilters();
		} else {
			$sort      = $this->primary_key;
			$filter    = [];
		}

		if (NULL === $this->dataModel) {
			throw new DataGridException('You have to set a data source first.');
		}

		$rows = [];

		$items = Nette\Utils\Callback::invokeArgs(
			[$this->dataModel, 'filterData'], [NULL, $sort, $filter]
		);

		foreach ($items as $item) {
			$rows[] = new Row($this, $item, $this->getPrimaryKey());
		}

		if ($export instanceof Export\ExportCsv) {
			$export->invoke($rows, $this);
		} else {
			$export->invoke($items, $this);
		}

		if ($export->isAjax()) {
			$this->reload();
		}
	}


	/**
	 * Handler for getting children of parent item (e.g. category)
	 * @param  int $parent
	 * @return void
	 */
	public function handleGetChildren($parent)
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
	 * @param  mixed $id
	 * @return void
	 */
	public function handleGetItemDetail($id)
	{
		$this->template->toggle_detail = $id;
		$this->redraw_item = [$this->items_detail->getPrimaryWhereColumn() => $id];

		if ($this->getPresenter()->isAjax()) {
			$this->getPresenter()->payload->_datagrid_toggle_detail = $id;
			$this->redrawControl('items');

			$this->onRedraw();
		} else {
			$this->getPresenter()->redirect('this');
		}
	}


	/**
	 * Handler for inline editing
	 * @param  mixed $id
	 * @param  mixed $key
	 * @return void
	 */
	public function handleEdit($id, $key)
	{
		$column = $this->getColumn($key);
		$value = $this->getPresenter()->getRequest()->getPost('value');

		call_user_func_array($column->getEditableCallback(), [$id, $value]);
	}


	/**
	 * Redraw $this
	 * @return void
	 */
	public function reload($snippets = [])
	{
		if ($this->getPresenter()->isAjax()) {
			$this->redrawControl('tbody');
			$this->redrawControl('pagination');

			/**
			 * manualy reset exports links...
			 */
			$this->resetExportsLinks();
			$this->redrawControl('exports');

			foreach ($snippets as $snippet) {
				$this->redrawControl($snippet);
			}

			$this->getPresenter()->payload->_datagrid_url = $this->refresh_url;

			$this->onRedraw();
		} else {
			$this->getPresenter()->redirect('this');
		}
	}


	/**
	 * Redraw just one row via ajax
	 * @param  int   $id
	 * @param  mixed $primary_where_column
	 * @return void
	 */
	public function redrawItem($id, $primary_where_column = NULL)
	{
		$this->redraw_item = [($primary_where_column ?: $this->primary_key) => $id];

		$this->redrawControl('items');
		$this->getPresenter()->payload->_datagrid_url = $this->refresh_url;

		$this->onRedraw();
	}


	/********************************************************************************
	 *                                  Components                                  *
	 ********************************************************************************/


	/**
	 * Paginator factory
	 * @return Components\DataGridPaginator\DataGridPaginator
	 */
	public function createComponentPaginator()
	{
		/**
		 * Init paginator
		 */
		$component = new Components\DataGridPaginator\DataGridPaginator;
		$paginator = $component->getPaginator();

		$paginator->setPage($this->page);
		$paginator->setItemsPerPage($this->getPerPage());

		return $component;
	}


	/**
	 * PerPage form factory
	 * @return Form
	 */
	public function createComponentPerPage()
	{
		$form = new Form;

		$form->addSelect('per_page', '', $this->getItemsPerPageList())
			->setValue($this->getPerPage());

		$form->addSubmit('submit', '');

		$saveSessionData = [$this, 'saveSessionData'];

		$form->onSuccess[] = function($form, $values) use ($saveSessionData) {
			/**
			 * Session stuff
			 */
			$saveSessionData('_grid_per_page', $values->per_page);

			/**
			 * Other stuff
			 */
			$this->per_page = $values->per_page;
			$this->reload();
		};

		return $form;
	}


	/**
	 * FilterAndGroupAction form factory
	 * @return Form
	 */
	public function createComponentFilter()
	{
		$form = new Form($this, 'filter');

		$form->setMethod('get');

		/**
		 * Filter part
		 */
		$filter_container = $form->addContainer('filter');

		foreach ($this->filters as $filter) {
			$filter->addToFormContainer($filter_container, $filter_container);
		}

		/**
		 * Group action part
		 */
		$group_action_container = $form->addContainer('group_action');

		if ($this->hasGroupActions()) {
			$this->getGroupActionCollection()->addToFormContainer($group_action_container, $form, $this->getTranslator());
		}

		$form->setDefaults(['filter' => $this->filter]);

		$form->onSubmit[] = [$this, 'filterSucceeded'];

		return $form;
	}


	/**
	 * Set $this->filter values after filter form submitted
	 * @param  Form $form
	 * @return void
	 */
	public function filterSucceeded(Form $form)
	{
		$values = $form->getValues();

		if ($this->getPresenter()->isAjax()) {
			if (isset($form['group_action']['submit']) && $form['group_action']['submit']->isSubmittedBy()) {
				return;
			}
		}

		$values = $values['filter'];

		foreach ($values as $key => $value) {
			/**
			 * Session stuff
			 */
			$this->saveSessionData($key, $value);

			/**
			 * Other stuff
			 */
			$this->filter[$key] = $value;
		}

		$this->reload();
	}


	/********************************************************************************
	 *                               Support functions                              *
	 ********************************************************************************/


	public function resetExportsLinks()
	{
		foreach ($this->exports as $id => $export) {
			$export->setLink($this->link('export!', ['id' => $id]));
		}
	}


	/**
	 * Get parameter per_page
	 * @return int
	 */
	public function getPerPage()
	{
		$per_page = $this->per_page ?: reset($this->items_per_page_list);

		if ($per_page !== 'all' && !in_array($this->per_page, $this->items_per_page_list)) {
			$per_page = reset($this->items_per_page_list);
		}

		return $per_page;
	}


	/**
	 * Get associative array of items_per_page_list
	 * @return array
	 */
	public function getItemsPerPageList()
	{
		$list = array_flip($this->items_per_page_list);

		foreach ($list as $key => $value) {
			$list[$key] = $key;
		}

		$list['all'] = $this->getTranslator()->translate('All');

		return $list;
	}


	/**
	 * Get primary key of datagrid data source
	 * @return string
	 */
	public function getPrimaryKey()
	{
		return $this->primary_key;
	}


	/**
	 * Get set of set columns
	 * @return Column\IColumn[]
	 */
	public function getColumns()
	{
		return $this->columns;
	}


	/**
	 * Has datagrid some group actions?
	 * @return boolean
	 */
	public function hasGroupActions()
	{
		return (bool) $this->group_action_collection;
	}


	/**
	 * Get translator for datagrid
	 * @return Nette\Localization\ITranslator
	 */
	public function getTranslator()
	{
		if (!$this->translator) {
			$this->translator = new Localization\SimpleTranslator;
		}

		return $this->translator;
	}


	/**
	 * Set datagrid translator
	 * @param Nette\Localization\ITranslator $translator
	 */
	public function setTranslator(Nette\Localization\ITranslator $translator)
	{
		$this->translator = $translator;

		return $this;
	}


	/**
	 * Should be datagrid filters rendered separately?
	 * @param boolean $out
	 */
	public function setOuterFilterRendering($out = TRUE)
	{
		$this->outer_filter_rendering = (bool) $out;

		return $this;
	}


	/**
	 * Are datagrid filters rendered separately?
	 * @return boolean
	 */
	public function hasOuterFilterRendering()
	{
		return $this->outer_filter_rendering;
	}


	/**
	 * Set order of datagrid columns
	 * @param array $order
	 */
	public function setColumnsOrder($order)
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
	 * @param array $order
	 */
	public function setColumnsExportOrder($order)
	{
		$this->columns_export_order = (array) $order;
	}


	/**
	 * Should datagrid remember its filters/pagination/etc using session?
	 * @param bool $remember
	 */
	public function setRememberState($remember = TRUE)
	{
		$this->remember_state = (bool) $remember;

		return $this;
	}


	/**
	 * Should datagrid refresh url using history API?
	 * @param bool $refresh
	 */
	public function setRefreshUrl($refresh = TRUE)
	{
		$this->refresh_url = (bool) $refresh;


		return $this;
	}


	/**
	 * Get session data if functionality is enabled
	 * @param  string $key
	 * @return mixed
	 */
	public function getSessionData($key = NULL)
	{
		if (!$this->remember_state) {
			return $key ? NULL : [];
		}

		return $key ? $this->grid_session->{$key} : $this->grid_session;
	}


	/**
	 * Save session data - just if it is enabled
	 * @param  string $key
	 * @param  mixed  $value
	 * @return void
	 */
	public function saveSessionData($key, $value)
	{

		if ($this->remember_state) {
			$this->grid_session->{$key} = $value;
		}
	}


	/**
	 * Delete session data
	 * @return void
	 */
	public function deleteSesssionData($key)
	{
		unset($this->grid_session->{$key});
	}


	/**
	 * Get items detail parameters
	 * @return array
	 */
	public function getItemsDetail()
	{
		return $this->items_detail;
	}


	/**
	 * Items can have thair detail - toggled
	 * @param mixed $detail callable|string|bool
	 */
	public function setItemsDetail($detail = TRUE, $primary_where_column = NULL)
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

		} else if (is_callable($detail)) {
			/**
			 * Item detail will be rendered via custom callback renderer
			 */
			$this->items_detail->setType('renderer');
			$this->items_detail->setRenderer($detail);

		} else if (TRUE === $detail) {
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
	 * Get cont of columns
	 * @return int
	 */
	public function getColumnsCount()
	{
		$count = sizeof($this->columns);

		if ($this->actions || $this->isSortable() || $this->getItemsDetail()) {
			$count++;
		}

		if ($this->hasGroupActions()) {
			$count++;
		}

		return $count;
	}


	public function allowRowsGroupAction(callable $condition)
	{
		$this->row_conditions['group_action'] = $condition;
	}


	public function allowRowsAction($key, callable $condition)
	{
		$this->row_conditions['action'][$key] = $condition;
	}


	public function getRowCondition($name, $key = NULL)
	{
		if (!isset($this->row_conditions[$name])) {
			return FALSE;
		}

		$condition = $this->row_conditions[$name];

		if (!$key) {
			return $condition;
		}

		return isset($condition[$key]) ? $condition[$key] : FALSE;
	}

}


class DataGridException extends \Exception
{
}
