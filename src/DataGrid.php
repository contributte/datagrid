<?php

/**
 * * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid;

use Nette,
	Ublaboo\DataGrid\Utils\ArraysHelper;

class DataGrid extends Nette\Application\UI\Control
{

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
	protected $items_per_page_list = [50, 100, 200];

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


	public function __construct(Nette\ComponentModel\IContainer $parent = NULL, $name = NULL)
	{
		if (!$parent) {
			throw new DataGridException(
				'Please create instance of DataGrid throw factory, or with [$parent] in constructor argument'
			);
		}

		parent::__construct($parent, $name);

		/**
		 * Try to find previous filters/pagination/sort in session
		 */
		$this->findSessionFilters();
	}


	/**
	 * Render template
	 * @return void
	 */
	public function render()
	{
		$this->template->setTranslator($this->getTranslator());
		/**
		 * Check whether grido has set some columns, initiated data source, atc
		 */
		$this->initCheck();

		/**
		 * Invoke some possible events
		 */
		$this->onRender($this);

		/**
		 * Prepare data for rendering (datagrid may render just one item)
		 */
		if ($this->redraw_item) {
			$this->template->items = $this->dataModel->filterRow($this->redraw_item);
		} else {
			$this->template->items = Nette\Utils\Callback::invokeArgs(
				[$this->dataModel, 'filterData'],
				[
					$this->getPaginator(),
					$this->sort,
					$this->assableFilters()
				]
			);
		}

		if ($this->isTreeView()) {
			$this->template->tree_view_has_children_column = $this->tree_view_has_children_column;
		}

		$this->template->columns = $this->columns;
		$this->template->actions = $this->actions;
		$this->template->exports = $this->exports;
		$this->template->filters = $this->filters;

		$this->template->filter_active = $this->isFilterActive();
		$this->template->original_template = $this->getOriginalTemplateFile();

		/**
		 * Set template file and render it
		 */
		$this->template->setFile($this->getTemplateFile())->render();
	}


	public function initCheck()
	{
		if (!($this->dataModel instanceof DataModel)) {
			throw new DataGridException('You have to set a data source first.');
		}

		if (empty($this->columns)) {
			throw new DataGridException('You have to add at least one column.');
		}
	}


	/**
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
	 * @param DataSource\IDataSource|array|\DibiFluent $data_source
	 * @return DataGrid
	 */
	public function setDataSource($data_source)
	{
		if ($data_source instanceof DataSource\IDataSource) {
			// $data_source is ready for interact

		} else if (is_array($data_source)) {
			$data_source = new DataSource\ArrayDataSource($data_source);


		} else if ($data_source instanceof \DibiFluent) {
			$driver = $data_source->getConnection()->getDriver();

			if ($driver instanceof \DibiOdbcDriver) {
				$data_source = new DataSource\DibiMssqlDataSource($data_source, $this->primary_key);

			} else if ($driver instanceof \DibiMsSqlDriver) {
				$data_source = new DataSource\DibiMssqlDataSource($data_source, $this->primary_key);

			} else {
				$data_source = new DataSource\DibiDataSource($data_source, $this->primary_key);
			}

		} else {
			$data_source_class = $data_source ? get_class($data_source) : 'NULL';
			throw new DataGridException("DataGrid can not take [$data_source_class] as data source.");
		}

		$this->dataModel = new DataModel($data_source);

		return $this;
	}


	public function isFilterActive()
	{
		return ((bool) $this->filter) || $this->force_filter_active;
	}


	public function setFilterActive()
	{
		$this->force_filter_active = TRUE;

		return $this;
	}


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
		return __DIR__ . '/templates/data_grid.latte';
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
		$this->sortable = (bool) $sortable;

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
	 * Is tree view set?
	 * @return boolean
	 */
	public function isTreeView()
	{
		return (bool) $this->tree_view_children_callback;
	}


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
			$this->setTemplateFile(__DIR__ . '/templates/data_grid_tree.latte');
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
	public function addColumnLink($key, $name, $column = NULL, $href = NULL, array $params = NULL)
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
	public function addFilterText($key, $name, $columns)
	{
		if ($this->dataModel->getDataSource() instanceof DataSource\ArrayDataSource) {
			throw new DataGridException('Filtering in array is not implemented yet');
		}

		if (is_string($columns)) {
			$columns = [$columns];
		}

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

		if ($this->dataModel->getDataSource() instanceof DataSource\ArrayDataSource) {
			throw new DataGridException('Filtering in array is not implemented yet');
		}

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

		if ($this->dataModel->getDataSource() instanceof DataSource\ArrayDataSource) {
			throw new DataGridException('Filtering in array is not implemented yet');
		}

		if (!is_string($column)) {
			throw new DataGridException("Filter Date can only filter through one column.");
		}

		$this->addFilterCheck($key);

		return $this->filters[$key] = new Filter\FilterDate($key, $name, $column);
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

		if ($this->dataModel->getDataSource() instanceof DataSource\ArrayDataSource) {
			throw new DataGridException('Filtering in array is not implemented yet');
		}

		if (!is_string($column)) {
			throw new DataGridException("Filter Date can only filter through one column.");
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
	 * @return array $this->filters === Filter\Filter[]
	 */
	public function assableFilters()
	{
		foreach ($this->filter as $key => $value) {
			if (is_array($value) || $value instanceof \Traversable) {
				if (!ArraysHelper::testEmpty($value)) {
					$this->filters[$key]->setValue($value);
				}
			} else {
				if ($value !== '') {
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
		if ($this->filter) {
			return;
		}

		$grid_session = $this->getPresenter()->getSession($this->getName());

		if ($grid_session->_grid_page) {
			$this->page = $grid_session->_grid_page;
		}

		if ($grid_session->_grid_per_page) {
			$this->page = $grid_session->_grid_per_page;
		}

		if ($grid_session->_grid_sort) {
			$this->sort = $grid_session->_grid_sort;
		}

		foreach ($grid_session as $key => $value) {
			if (!in_array($key, ['_grid_per_page', '_grid_sort', '_grid_page'])) {
				$this->filter[$key] = $value;
			}
		}
	}


	/********************************************************************************
	 *                                    Exports                                   *
	 ********************************************************************************/


	public function addExportCallback($text, $callback, $filtered = FALSE)
	{
		if (!is_callable($callback)) {
			throw new DataGridException("Second parameter of ExportCallback must be callable.");
		}

		return $this->addToExports(new Export\Export($text, $callback, $filtered));
	}


	public function addExportCsv($text, $csv_file_name)
	{
		return $this->addToExports(new Export\ExportCsv($text, $csv_file_name, FALSE));
	}


	public function addExportCsvFiltered($text, $csv_file_name)
	{
		return $this->addToExports(new Export\ExportCsv($text, $csv_file_name, TRUE));
	}


	protected function addToExports(Export\Export $export)
	{
		$id = ($s = sizeof($this->exports)) ? ($s + 1) : 1;

		$export->setLink($this->link('export!', ['id' => $id]));

		return $this->exports[$id] = $export;
	}


	/********************************************************************************
	 *                                 Group actions                                *
	 ********************************************************************************/


	public function addGroupAction($title, $options = [])
	{
		return $this->getGroupActionCollection()->addGroupAction($title, $options);
	}


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
		$grid_session = $this->getPresenter()->getSession($this->getName());
		$grid_session->_grid_page = $page;

		$this->reload();
	}


	public function handleSort()
	{
		/**
		 * Session stuff
		 */
		$grid_session = $this->getPresenter()->getSession($this->getName());
		$grid_session->_grid_sort = $page;

		$this->reload();
	}


	public function handleResetFilter()
	{
		/**
		 * Session stuff
		 */
		$grid_session = $this->getPresenter()->getSession($this->getName());

		foreach ($grid_session as $key => $value) {
			if (!in_array($key, ['_grid_per_page', '_grid_sort', '_grid_page'])) {
				unset($grid_session->{$key});
			}
		}

		$this->filter = [];
		$this->reload(['filter']);
		$this->template->just_reset = TRUE;
	}


	public function handleRedrawAll()
	{
		$this->reload(['filter']);
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

		$export = $this->exports[$id];

		if ($export->isFiltered()) {
			$sort      = $this->sort;
			$filter    = $this->assableFilters();
		} else {
			$sort      = $this->primary_key;
			$filter    = [];
		}

		$data = Nette\Utils\Callback::invokeArgs(
			[$this->dataModel, 'filterData'], [NULL, $sort, $filter]
		);

		$export->invoke($data, $this);

		if ($filter->isAjax()) {
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
			$this->redrawControl('items');
		} else {
			$this->getPresenter()->redirect('this');
		}
	}


	/**
	 * Redraw $this
	 * @return void
	 */
	public function reload($snippets = [])
	{
		if ($this->getPresenter()->isAjax()) {
			$this->redrawControl('data');
			$this->redrawControl('pagination');

			/**
			 * manualy reset exports links...
			 */
			$this->resetExportsLinks();
			$this->redrawControl('exports');

			foreach ($snippets as $snippet) {
				$this->redrawControl($snippet);
			}
		} else {
			$this->getPresenter()->redirect('this');
		}
	}


	public function redrawItem($id, $primary_where_column = NULL)
	{
		$this->redraw_item = [($primary_where_column?: $this->primary_key) => $id];

		$this->redrawControl('items');
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
	 * @return Nette\Application\UI\Form
	 */
	public function createComponentPerPage()
	{
		$form = new Nette\Application\UI\Form;

		$form->addSelect('per_page', '', $this->getItemsPerPageList())
			->setValue($this->getPerPage());

		$form->addSubmit('submit', '');

		$grid_session = $this->getPresenter()->getSession($this->getName());

		$form->onSuccess[] = function($form, $values) {
			/**
			 * Session stuff
			 */
			$grid_session->_grid_per_page = $values->per_page;

			/**
			 * Other stuff
			 */
			$this->per_page = $values->per_page;
			$this->reload();
		};

		return $form;
	}


	/**
	 * Filter form factory
	 * @return Nette\Application\UI\Form
	 */
	public function createComponentFilter()
	{
		$form = new Nette\Application\UI\Form;

		$form->setMethod('get');

		foreach ($this->filters as $filter) {
			$filter->addToForm($form);
		}

		$form->setDefaults($this->filter);

		$form->onSuccess[] = [$this, 'filterFormSucceeded'];

		return $form;
	}


	/**
	 * Set $this->filter values after filter form submitted
	 * @param  Nette\Application\UI\Form $form
	 * @param  Nette\Utils\ArrayHash     $values
	 * @return void
	 */
	public function filterFormSucceeded($form, $values)
	{
		$grid_session = $this->getPresenter()->getSession($this->getName());

		foreach ($values as $key => $value) {
			/**
			 * Session stuff
			 */
			$grid_session->{$key} = $value;

			/**
			 * Other stuff
			 */
			$this->filter[$key] = $value;
		}

		$this->reload();
	}


	/**
	 * Group actions form factory
	 * @return Nette\Application\UI\Form
	 */
	public function createComponentGroupAction()
	{
		if ($this->hasGroupActions()) {
			return $this->getGroupActionCollection()->getFormComponent();
		}

		return new Nette\Application\UI\Form;
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

		$list['all'] = $this->getTranslator()->translate('Vše');

		return $list;
	}


	public function getPrimaryKey()
	{
		return $this->primary_key;
	}


	public function getColumns()
	{
		return $this->columns;
	}


	public function hasGroupActions()
	{
		return (bool) $this->group_action_collection;
	}


	public function getTranslator()
	{
		if (!$this->translator) {
			$this->translator = new Localization\SimpleTranslator;
		}

		return $this->translator;
	}


	public function setTranslator(Nette\Localization\ITranslator $translator)
	{
		$this->translator = $translator;

		return $this;
	}

}


class DataGridException extends \Exception
{
}
