<?php declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid;

use Nette;
use Nette\Application\IPresenter;
use Nette\Application\UI\Component;
use Nette\Application\UI\Form;
use Nette\Application\UI\Link;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\PresenterComponent;
use Nette\ComponentModel\IContainer;
use Nette\Forms\Container;
use Nette\Http\SessionSection;
use Nette\Localization\ITranslator;
use Ublaboo\DataGrid\AggregationFunction\TDataGridAggregationFunction;
use Ublaboo\DataGrid\Column\Action;
use Ublaboo\DataGrid\Column\ActionCallback;
use Ublaboo\DataGrid\Column\Column;
use Ublaboo\DataGrid\Column\ColumnDateTime;
use Ublaboo\DataGrid\Column\ColumnLink;
use Ublaboo\DataGrid\Column\ColumnNumber;
use Ublaboo\DataGrid\Column\ColumnStatus;
use Ublaboo\DataGrid\Column\ColumnText;
use Ublaboo\DataGrid\Column\ItemDetail;
use Ublaboo\DataGrid\Column\MultiAction;
use Ublaboo\DataGrid\ColumnsSummary;
use Ublaboo\DataGrid\Components\DataGridPaginator\DataGridPaginator;
use Ublaboo\DataGrid\DataSource\IDataSource;
use Ublaboo\DataGrid\Exception\DataGridColumnNotFoundException;
use Ublaboo\DataGrid\Exception\DataGridException;
use Ublaboo\DataGrid\Exception\DataGridFilterNotFoundException;
use Ublaboo\DataGrid\Exception\DataGridHasToBeAttachedToPresenterComponentException;
use Ublaboo\DataGrid\Export\Export;
use Ublaboo\DataGrid\Export\ExportCsv;
use Ublaboo\DataGrid\Filter\Filter;
use Ublaboo\DataGrid\Filter\FilterDateRange;
use Ublaboo\DataGrid\Filter\FilterMultiSelect;
use Ublaboo\DataGrid\Filter\FilterRange;
use Ublaboo\DataGrid\Filter\FilterText;
use Ublaboo\DataGrid\Filter\IFilterDate;
use Ublaboo\DataGrid\Filter\SubmitButton;
use Ublaboo\DataGrid\GroupAction\GroupAction;
use Ublaboo\DataGrid\GroupAction\GroupActionCollection;
use Ublaboo\DataGrid\InlineEdit\InlineEdit;
use Ublaboo\DataGrid\Localization\SimpleTranslator;
use Ublaboo\DataGrid\Toolbar\ToolbarButton;
use Ublaboo\DataGrid\Utils\ArraysHelper;
use Ublaboo\DataGrid\Utils\ItemDetailForm;
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
	public $onRedraw = [];

	/**
	 * @var callable[]
	 */
	public $onRender = [];

	/**
	 * @var callable[]
	 */
	public $onExport = [];

	/**
	 * @var callable[]
	 */
	public $onColumnAdd = [];

	/**
	 * @var callable[]
	 */
	public $onFiltersAssembled = [];

	/**
	 * @var string
	 */
	public static $iconPrefix = 'fa fa-';

	/**
	 * Default form method
	 * @var string
	 */
	public static $formMethod = 'post';

	/**
	 * When set to TRUE, datagrid throws an exception
	 * 	when tring to get related entity within join and entity does not exist
	 * @var bool
	 */
	public $strictEntityProperty = false;

	/**
	 * When set to TRUE, datagrid throws an exception
	 * 	when tring to set filter value, that does not exist (select, multiselect, etc)
	 * @var bool
	 */
	public $strictSessionFilterValues = true;

	/**
	 * @var int
	 * @persistent
	 */
	public $page = 1;

	/**
	 * @var int|string|null
	 * @persistent
	 */
	public $perPage = null;

	/**
	 * @var array
	 * @persistent
	 */
	public $sort = [];

	/**
	 * @var array
	 */
	public $defaultSort = [];

	/**
	 * @var array
	 */
	public $defaultFilter = [];

	/**
	 * @var bool
	 */
	public $defaultFilterUseOnReset = true;

	/**
	 * @var bool
	 */
	public $defaultSortUseOnReset = true;

	/**
	 * @var array
	 * @persistent
	 */
	public $filter = [];

	/**
	 * @var callable|null
	 */
	protected $sortCallback = null;

	/**
	 * @var bool
	 */
	protected $useHappyComponents = true;

	/**
	 * @var callable
	 */
	protected $rowCallback;

	/**
	 * @var array
	 */
	protected $itemsPerPageList = [10, 20, 50, 'all'];

	/**
	 * @var int|null
	 */
	protected $defaultPerPage = null;

	/**
	 * @var string|null
	 */
	protected $templateFile = null;

	/**
	 * @var Column[]
	 */
	protected $columns = [];

	/**
	 * @var Action[]
	 */
	protected $actions = [];

	/**
	 * @var GroupActionCollection|null
	 */
	protected $groupActionCollection;

	/**
	 * @var Filter[]
	 */
	protected $filters = [];

	/**
	 * @var Export[]
	 */
	protected $exports = [];

	/**
	 * @var ToolbarButton[]
	 */
	protected $toolbarButtons = [];

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
	protected $primaryKey = 'id';

	/**
	 * @var bool
	 */
	protected $doPaginate = true;

	/**
	 * @var bool
	 */
	protected $csvExport = true;

	/**
	 * @var bool
	 */
	protected $csvExportFiltered = true;

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
	protected $sortableHandler = 'sort!';

	/**
	 * @var string|null
	 */
	protected $originalTemplate = null;

	/**
	 * @var array
	 */
	protected $redrawItem = [];

	/**
	 * @var ITranslator|null
	 */
	protected $translator = null;

	/**
	 * @var bool
	 */
	protected $forceFilterActive = false;

	/**
	 * @var callable|null
	 */
	protected $treeViewChildrenCallback = null;

	/**
	 * @var callable|null
	 */
	protected $treeViewHasChildrenCallback = null;

	/**
	 * @var string|null
	 */
	protected $treeViewHasChildrenColumn = null;

	/**
	 * @var bool
	 */
	protected $outerFilterRendering = false;

	/**
	 * @var int
	 */
	protected $outerFilterColumnsCount = 2;

	/**
	 * @var bool
	 */
	protected $collapsibleOuterFilters = true;

	/**
	 * @var array
	 */
	protected $columnsExportOrder = [];

	/**
	 * @var bool
	 */
	protected $rememberState = true;

	/**
	 * @var bool
	 */
	protected $refreshURL = true;

	/**
	 * @var SessionSection
	 */
	protected $gridSession;

	/**
	 * @var ItemDetail
	 */
	protected $itemsDetail;

	/**
	 * @var array
	 */
	protected $rowConditions = [
		'group_action' => false,
		'action' => [],
	];

	/**
	 * @var array
	 */
	protected $columnCallbacks = [];

	/**
	 * @var bool
	 */
	protected $canHideColumns = false;

	/**
	 * @var array
	 */
	protected $columnsVisibility = [];

	/**
	 * @var InlineEdit|null
	 */
	protected $inlineEdit = null;

	/**
	 * @var InlineEdit|null
	 */
	protected $inlineAdd = null;

	/**
	 * @var bool
	 */
	protected $snippetsSet = false;

	/**
	 * @var bool
	 */
	protected $someColumnDefaultHide = false;

	/**
	 * @var ColumnsSummary
	 */
	protected $columnsSummary;

	/**
	 * @var bool
	 */
	protected $autoSubmit = true;

	/**
	 * @var SubmitButton|null
	 */
	protected $filterSubmitButton = null;

	/**
	 * @var bool
	 */
	protected $hasColumnReset = true;

	/**
	 * @var bool
	 */
	protected $showSelectedRowsCount = true;

	/**
	 * @var string|null
	 */
	private $customPaginatorTemplate = null;


	public function __construct(?IContainer $parent = null, ?string $name = null)
	{
		parent::__construct();

		if ($parent !== null) {
			$parent->addComponent($this, $name);
		}

		$this->monitor('Nette\Application\UI\Presenter');

		/**
		 * Try to find previous filters, pagination, perPage and other values in session
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

		$this->monitor(
			Presenter::class,
			function(Presenter $presenter): void {
				/**
				 * Get session
				 */
				if ($this->rememberState) {
					$this->gridSession = $presenter->getSession($this->getSessionSectionName());
				}
			}
		);
	}


	/********************************************************************************
	 *                                  RENDERING                                   *
	 ********************************************************************************/


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

		if (!empty($this->redrawItem)) {
			$items = $this->dataModel->filterRow($this->redrawItem);
		} else {
			$items = Nette\Utils\Callback::invokeArgs(
				[$this->dataModel, 'filterData'],
				[
					$this->getPaginator(),
					$this->createSorting($this->sort, $this->sortCallback),
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
			if (!empty($this->redrawItem)) {
				$this->getPresenter()->payload->_datagrid_redrawItem_class = $row->getControlClass();
				$this->getPresenter()->payload->_datagrid_redrawItem_id = $row->getId();
			}
		}

		if ($hasGroupActionOnRows) {
			$hasGroupActionOnRows = $this->hasGroupActions();
		}

		if ($this->isTreeView()) {
			$template->add('treeViewHasChildrenColumn', $this->treeViewHasChildrenColumn);
		}

		$template->rows = $rows;

		$template->columns = $this->getColumns();
		$template->actions = $this->actions;
		$template->exports = $this->exports;
		$template->filters = $this->filters;
		$template->toolbarButtons = $this->toolbarButtons;
		$template->aggregation_functions = $this->getAggregationFunctions();
		$template->multiple_aggregation_function = $this->getMultipleAggregationFunction();

		$template->filter_active = $this->isFilterActive();
		$template->originalTemplate = $this->getOriginalTemplateFile();
		$template->iconPrefix = static::$iconPrefix;
		$template->iconPrefix = static::$iconPrefix;
		$template->itemsDetail = $this->itemsDetail;
		$template->columnsVisibility = $this->getColumnsVisibility();
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
	 * Each row can be modified with user defined callback
s	 */
	public function setRowCallback(callable $callback): self
	{
		$this->rowCallback = $callback;

		return $this;
	}


	/********************************************************************************
	 *                                 DATA SOURCE                                  *
	 ********************************************************************************/


	public function setPrimaryKey(string $primaryKey): self
	{
		if ($this->dataModel instanceof DataModel) {
			throw new DataGridException(
				'Please set datagrid primary key before setting datasource.'
			);
		}

		$this->primaryKey = $primaryKey;

		return $this;
	}


	/**
	 * @param IDataSource|array
	 * @throws \InvalidArgumentException
	 */
	public function setDataSource($source): self
	{
		if (!is_array($source) && !$source instanceof IDataSource) {	
			throw new \InvalidArgumentException(
				sprintf('Please provide an instance of %s or an array', IDataSource::class)
			)
		}

		$this->dataModel = new DataModel($source, $this->primaryKey);

		$this->dataModel->onBeforeFilter[] = [$this, 'beforeDataModelFilter'];
		$this->dataModel->onAfterFilter[] = [$this, 'afterDataModelFilter'];
		$this->dataModel->onAfterPaginated[] = [$this, 'afterDataModelPaginated'];

		return $this;
	}


	/**
	 * @return DataSource\IDataSource|null
	 */
	public function getDataSource()
	{
		if (!$this->dataModel) {
			return null;
		}

		return $this->dataModel->getDataSource();
	}


	/********************************************************************************
	 *                                  TEMPLATING                                  *
	 ********************************************************************************/


	public function setTemplateFile(string $templateFile): self
	{
		$this->templateFile = $templateFile;

		return $this;
	}


	public function getTemplateFile(): string
	{
		return $this->templateFile ?: $this->getOriginalTemplateFile();
	}


	public function getOriginalTemplateFile(): string
	{
		return __DIR__ . '/templates/datagrid.latte';
	}


	public function useHappyComponents(bool $useHappyComponents): self
	{
		$this->useHappyComponents = $use;

		return $this;
	}


	public function shouldUseHappyComponents(): bool
	{
		return $this->useHappyComponents;
	}


	/********************************************************************************
	 *                                   SORTING                                    *
	 ********************************************************************************/


	public function setDefaultSort(array $sort, bool $useOnReset = true): self
	{
		if (is_string($sort)) {
			$sort = [$sort => 'ASC'];
		} else {
			$sort = (array) $sort;
		}

		$this->defaultSort = $sort;
		$this->defaultSortUseOnReset = (bool) $useOnReset;

		return $this;
	}


	/**
	 * Return default sort for column, if specified
	 */
	public function getColumnDefaultSort(string $columnKey): ?string
	{
		if (isset($this->defaultSort[$columnKey])) {
			return $this->defaultSort[$columnKey];
		}

		return null;
	}


	/**
	 * User may set default sorting, apply it
	 */
	public function findDefaultSort(): void
	{
		if ($this->sort !== []) {
			return;
		}

		if ($this->getSessionData('_grid_has_sorted')) {
			return;
		}

		if ($this->defaultSort !== []) {
			$this->sort = $this->defaultSort;
		}

		$this->saveSessionData('_grid_sort', $this->sort);
	}


	/**
	 * @throws DataGridException
	 */
	public function setSortable(bool $sortable = true): self
	{
		if ($this->getItemsDetail()) {
			throw new DataGridException('You can not use both sortable datagrid and items detail.');
		}

		$this->sortable = (bool) $sortable;

		return $this;
	}


	public function isSortable(): bool
	{
		return $this->sortable;
	}


	public function setMultiSortEnabled(bool $multiSort = true): self
	{
		$this->multiSort = $multiSort;

		return $this;
	}


	public function isMultiSortEnabled(): bool
	{
		return $this->multiSort;
	}


	public function setSortableHandler(string $handler = 'sort!'): self
	{
		$this->sortableHandler = $handler;

		return $this;
	}


	public function getSortableHandler(): string
	{
		return $this->sortableHandler;
	}


	public function getSortNext(Column $column): array
	{
		$sort = $column->getSortNext();

		if ($this->isMultiSortEnabled()) {
			$sort = array_merge($this->sort, $sort);
		}

		return array_filter($sort);
	}


	protected function createSorting(array $sort, ?callable $sortCallback = null): Sorting
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

		if (!$sortCallback && isset($column)) {
			$sortCallback = $column->getSortableCallback();
		}

		return new Sorting($sort, $sortCallback);
	}


	/********************************************************************************
	 *                                  TREE VIEW                                   *
	 ********************************************************************************/


	public function isTreeView(): bool
	{
		return (bool) $this->treeViewChildrenCallback;
	}


	/**
	 * @param string|callable $treeViewHasChildrenColumn
	 */
	public function setTreeView(
		callable $getChildrenCallback,
		$treeViewHasChildrenColumn = 'has_children'
	): self
	{
		if (is_callable($treeViewHasChildrenColumn)) {
			$this->treeViewHasChildrenCallback = $treeViewHasChildrenColumn;
			$treeViewHasChildrenColumn = null;
		}

		$this->treeViewChildrenCallback = $getChildrenCallback;
		$this->treeViewHasChildrenColumn = $treeViewHasChildrenColumn;

		/**
		 * TUrn off pagination
		 */
		$this->setPagination(false);

		/**
		 * Set tree view template file
		 */
		if (!$this->templateFile) {
			$this->setTemplateFile(__DIR__ . '/templates/datagrid_tree.latte');
		}

		return $this;
	}


	public function hasTreeViewChildrenCallback(): bool
	{
		return is_callable($this->treeViewHasChildrenCallback);
	}


	/**
	 * @param  mixed $item
	 */
	public function treeViewChildrenCallback($item): bool
	{
		return (bool) call_user_func($this->treeViewHasChildrenCallback, $item);
	}


	/********************************************************************************
	 *                                    COLUMNS                                   *
	 ********************************************************************************/


	public function addColumnText(
		string $key,
		string $name,
		?string $column = null
	): ColumnText
	{
		$column = $column ?: $key;

		return $this->addColumn($key, new ColumnText($this, $key, $column, $name));
	}


	public function addColumnLink(
		string $key,
		string $name,
		?string $href = null,
		?string $column = null,
		?array $params = null
	): ColumnLink
	{
		$column = $column ?: $key;
		$href = $href ?: $key;

		if ($params === null) {
			$params = [$this->primaryKey];
		}

		return $this->addColumn($key, new ColumnLink($this, $key, $column, $name, $href, $params));
	}


	public function addColumnNumber(
		string $key,
		string $name,
		?string $column = null
	): ColumnNumber
	{
		$column = $column ?: $key;

		return $this->addColumn($key, new ColumnNumber($this, $key, $column, $name));
	}


	public function addColumnDateTime(
		string $key,
		string $name,
		?string $column = null
	): ColumnDateTime
	{
		$column = $column ?: $key;

		return $this->addColumn($key, new ColumnDateTime($this, $key, $column, $name));
	}


	public function addColumnStatus(
		string $key,
		string $name,
		?string $column = null
	): ColumnStatus
	{
		$column = $column ?: $key;

		return $this->addColumn($key, new ColumnStatus($this, $key, $column, $name));
	}


	/**
	 * @throws DataGridException
	 */
	protected function addColumn(string $key, Column $column): Column
	{
		if (isset($this->columns[$key])) {
			throw new DataGridException(
				sprintf('There is already column at key [%s] defined.', $key)
			);
		}

		$this->onColumnAdd($key, $column);

		$this->columnsVisibility[$key] = ['visible' => true];

		return $this->columns[$key] = $column;
	}


	/**
	 * @throws DataGridColumnNotFoundException
	 */
	public function getColumn(string $key): Column
	{
		if (!isset($this->columns[$key])) {
			throw new DataGridColumnNotFoundException(
				sprintf('There is no column at key [%s] defined.', $key)
			);
		}

		return $this->columns[$key];
	}


	public function removeColumn(string $key): self
	{
		unset($this->columnsVisibility[$key], $this->columns[$key]);

		return $this;
	}


	/********************************************************************************
	 *                                    ACTIONS                                   *
	 ********************************************************************************/


	public function addAction(
		string $key,
		string $name,
		?string $href = null,
		?array $params = null
	): Action
	{
		$this->addActionCheck($key);

		$href = $href ?: $key;

		if ($params === null) {
			$params = [$this->primaryKey];
		}

		return $this->actions[$key] = new Action($this, $href, $name, $params);
	}


	public function addActionCallback(
		string $key,
		string $name,
		?callable $callback = null
	): Action
	{
		$this->addActionCheck($key);

		$params = ['__id' => $this->primaryKey];

		$this->actions[$key] = $action = new ActionCallback($this, $key, $name, $params);

		if ($callback !== null) {
			$action->onClick[] = $callback;
		}

		return $action;
	}


	public function addMultiAction(string $key, string $name): MultiAction
	{
		$this->addActionCheck($key);

		$this->actions[$key] = $action = new MultiAction($this, $name);

		return $action;
	}


	/**
	 * @throws DataGridException
	 */
	public function getAction(string $key): Action
	{
		if (!isset($this->actions[$key])) {
			throw new DataGridException(sprintf('There is no action at key [%s] defined.', $key));
		}

		return $this->actions[$key];
	}


	public function removeAction(string $key): self
	{
		unset($this->actions[$key]);

		return $this;
	}


	/**
	 * Check whether given key already exists in $this->filters
	 * @throws DataGridException
	 */
	protected function addActionCheck(string $key): void
	{
		if (isset($this->actions[$key])) {
			throw new DataGridException(
				sprintf('There is already action at key [%s] defined.', $key)
			);
		}
	}


	/********************************************************************************
	 *                                    FILTERS                                   *
	 ********************************************************************************/


	/**
	 * @param array|string $columns
	 */
	public function addFilterText(
		string $key,
		string $name,
		$columns = null
	): FilterText
	{
		$columns = $columns === null ? [$key] : (is_string($columns) ? [$columns] : $columns);

		if (!is_array($columns)) {
			throw new DataGridException('Filter Text can accept only array or string.');
		}

		$this->addFilterCheck($key);

		return $this->filters[$key] = new FilterText($this, $key, $name, $columns);
	}


	public function addFilterSelect(
		string $key,
		string $name,
		array $options,
		?string $column = null
	): FilterSelect
	{
		$column = $column ?? $key;

		$this->addFilterCheck($key);

		return $this->filters[$key] = new FilterSelect($this, $key, $name, $options, $column);
	}


	public function addFilterMultiSelect(
		string $key,
		string $name,
		array $options,
		?string $column = null
	): FilterMultiSelect
	{
		$column = $column ?? $key;

		$this->addFilterCheck($key);

		return $this->filters[$key] = new FilterMultiSelect($this, $key, $name, $options, $column);
	}


	public function addFilterDate(string $key, string $name, ?string $column = null): FilterDate
	{
		$column = $column ?: $key;

		$this->addFilterCheck($key);

		return $this->filters[$key] = new FilterDate($this, $key, $name, $column);
	}


	public function addFilterRange(
		string $key,
		string $name,
		?string $column = null,
		?string $nameSecond = '-'
	): FilterRange
	{
		$column = $column ?? $key;

		$this->addFilterCheck($key);

		return $this->filters[$key] = new FilterRange(
			$this,
			$key,
			$name,
			$column,
			$nameSecond
		);
	}


	/**
	 * @throws DataGridException
	 */
	public function addFilterDateRange(
		string $key,
		string $name,
		?string $column = null,
		?string $nameSecond = '-'
	): FilterDateRange {
		$column = $column ?? $key;

		$this->addFilterCheck($key);

		return $this->filters[$key] = new FilterDateRange(
			$this,
			$key,
			$name,
			$column,
			$nameSecond
		);
	}


	/**
	 * Check whether given key already exists in $this->filters
	 * @throws DataGridException
	 */
	protected function addFilterCheck(string $key): void
	{
		if (isset($this->filters[$key])) {
			throw new DataGridException(
				sprintf('There is already action at key [%s] defined.', $key)
			);
		}
	}


	/**
	 * Fill array of Filter\Filter[] with values from $this->filter persistent parameter
	 * Fill array of Column\Column[] with values from $this->sort   persistent parameter
	 * @return array|Filter[]
	 */
	public function assembleFilters(): array
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

		$this->onFiltersAssembled($this->filters);

		return $this->filters;
	}


	public function removeFilter(string $key): self
	{
		unset($this->filters[$key]);

		return $this;
	}


	public function getFilter(string $key): Filter
	{
		if (!isset($this->filters[$key])) {
			throw new DataGridException(sprintf('Filter [%s] is not defined', $key));
		}

		return $this->filters[$key];
	}


	public function setStrictSessionFilterValues(bool $strictSessionFilterValues = true): self
	{
		$this->strictSessionFilterValues = $strictSessionFilterValues;

		return $this;
	}


	/********************************************************************************
	 *                                  FILTERING                                   *
	 ********************************************************************************/


	public function isFilterActive(): bool
	{
		$is_filter = ArraysHelper::testTruthy($this->filter);

		return ($is_filter) || $this->forceFilterActive;
	}


	/**
	 * Tell that filter is active from whatever reasons
	 */
	public function setFilterActive(): self
	{
		$this->forceFilterActive = true;

		return $this;
	}


	/**
	 * Set filter values (force - overwrite user data)
	 */
	public function setFilter(array $filter): self
	{
		$this->filter = $filter;

		$this->saveSessionData('_grid_has_filtered', 1);

		return $this;
	}


	/**
	 * If we want to sent some initial filter
	 * @throws DataGridException
	 */
	public function setDefaultFilter(array $defaultFilter, bool $useOnReset = true): self
	{
		foreach ($defaultFilter as $key => $value) {
			$filter = $this->getFilter($key);

			if (!$filter) {
				throw new DataGridException(
					sprintf('Can not set default value to nonexisting filter [%s]', $key)
				);
			}

			if ($filter instanceof FilterMultiSelect && !is_array($value)) {
				throw new DataGridException(
					sprintf('Default value of filter [%s] - MultiSelect has to be an array', $key)
				);
			}

			if ($filter instanceof FilterRange || $filter instanceof FilterDateRange) {
				if (!is_array($value) || !isset($value['from'], $value['to'])) {
					throw new DataGridException(
						sprintf(
							'Default value of filter [%s] - %s has to be an array [from/to => ...]',
							DateRange::class,
							$key
						)
					);
				}
			}
		}

		$this->defaultFilter = $defaultFilter;
		$this->defaultFilterUseOnReset = $useOnReset;

		return $this;
	}


	public function findDefaultFilter(): void
	{
		if (!empty($this->filter)) {
			return;
		}

		if ($this->getSessionData('_grid_has_filtered')) {
			return;
		}

		if (!empty($this->defaultFilter)) {
			$this->filter = $this->defaultFilter;
		}

		foreach ($this->filter as $key => $value) {
			$this->saveSessionData($key, $value);
		}
	}


	public function createComponentFilter(): Form
	{
		$form = new Form($this, 'filter');

		$form->setMethod(static::$formMethod);

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
		$itemsDetail_form = $this->getItemDetailForm();

		if ($itemsDetail_form instanceof Nette\Forms\Container) {
			$form['itemsDetail_form'] = $itemsDetail_form;
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
		$form->addSelect('perPage', '', $this->getItemsPerPageList())
			->setTranslator(null);

		if (!$form->isSubmitted()) {
			$form['perPage']->setValue($this->getPerPage());
		}

		$form->addSubmit('perPage_submit', 'ublaboo_datagrid.perPage_submit')
			->setValidationScope([$form['perPage']]);

		$form->onSubmit[] = [$this, 'filterSucceeded'];
	}


	public function setFilterContainerDefaults(Container $container, array $values): void
	{
		foreach ($container->getComponents() as $key => $control) {
			if (!isset($values[$key])) {
				continue;
			}

			if ($control instanceof Container) {
				$this->setFilterContainerDefaults($control, $values[$key]);

				continue;
			}

			$value = $values[$key];

			if ($value instanceof \DateTime && ($filter = $this->getFilter($key)) instanceof IFilterDate) {
				$value = $value->format($filter->getPhpFormat());
			}

			try {
				$control->setValue($value);

			} catch (\InvalidArgumentException $e) {
				if ($this->strictSessionFilterValues) {
					throw $e;
				}
			}
		}
	}


	/**
	 * Set $this->filter values after filter form submitted
	 */
	public function filterSucceeded(Form $form): void
	{
		if ($this->snippetsSet) {
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
		$this->saveSessionData('_grid_perPage', $values->perPage);
		$this->perPage = $values->perPage;

		/**
		 * Inline edit
		 */
		if (isset($form['inline_edit']) && isset($form['inline_edit']['submit']) && isset($form['inline_edit']['cancel'])) {
			$edit = $form['inline_edit'];

			if ($edit['submit']->isSubmittedBy() || $edit['cancel']->isSubmittedBy()) {
				$id = $form->getHttpData(Form::DATA_LINE, 'inline_edit[_id]');
				$primaryWhereColumn = $form->getHttpData(
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
					$this->redrawItem($id, $primaryWhereColumn);
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
			if ($this->rememberState && $this->getSessionData($key) != $value) {
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


	public function setOuterFilterRendering(bool $outerFilterRendering = true): self
	{
		$this->outerFilterRendering = $outerFilterRendering;

		return $this;
	}


	public function hasOuterFilterRendering(): bool
	{
		return $this->outerFilterRendering;
	}


	/**
	 * @throws \InvalidArgumentException
	 */
	public function setOuterFilterColumnsCount(int $count): self
	{
		$columnsCounts = [1, 2, 3, 4, 6, 12];

		if (!in_array($count, $columnsCounts, true)) {
			throw new \InvalidArgumentException(sprintf(
				'Columns count must be one of following values: %s. Value %s given.',
				implode(', ', $columnsCounts),
				$count
			));
		}

		$this->outerFilterColumnsCount = (int) $count;

		return $this;
	}


	public function getOuterFilterColumnsCount(): bool
	{
		return $this->outerFilterColumnsCount;
	}


	public function setCollapsibleOuterFilters(bool $collapsibleOuterFilters = true): self
	{
		$this->collapsibleOuterFilters = $collapsibleOuterFilters;

		return $this;
	}


	public function hasCollapsibleOuterFilters(): bool
	{
		return $this->collapsibleOuterFilters;
	}


	/**
	 * Try to restore session stuff
	 * @throws DataGridFilterNotFoundException
	 */
	public function findSessionValues(): void
	{
		if (!ArraysHelper::testEmpty($this->filter) || ($this->page != 1) || !empty($this->sort)) {
			return;
		}

		if (!$this->rememberState) {
			return;
		}

		if ($page = $this->getSessionData('_grid_page')) {
			$this->page = $page;
		}

		if ($perPage = $this->getSessionData('_grid_perPage')) {
			$this->perPage = $perPage;
		}

		if ($sort = $this->getSessionData('_grid_sort')) {
			$this->sort = $sort;
		}

		foreach ($this->getSessionData() as $key => $value) {
			$other_session_keys = [
				'_grid_perPage',
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
					if ($this->strictSessionFilterValues) {
						throw new DataGridFilterNotFoundException(
							sprintf('Session filter: Filter [%s] not found', $key)
						);
					}
				}
			}
		}

		/**
		 * When column is sorted via custom callback, apply it
		 */
		if (empty($this->sortCallback) && !empty($this->sort)) {
			foreach ($this->sort as $key => $order) {
				try {
					$column = $this->getColumn($key);

				} catch (DataGridColumnNotFoundException $e) {
					$this->deleteSessionData('_grid_sort');
					$this->sort = [];

					return;
				}

				if ($column && $column->isSortable() && is_callable($column->getSortableCallback())) {
					$this->sortCallback = $column->getSortableCallback();
				}
			}
		}
	}


	/********************************************************************************
	 *                                    EXPORTS                                   *
	 ********************************************************************************/


	public function addExportCallback(
		string $text,
		callable $callback,
		bool $filtered = false
	): Export {
		if (!is_callable($callback)) {
			throw new DataGridException('Second parameter of ExportCallback must be callable.');
		}

		return $this->addToExports(new Export($this, $text, $callback, $filtered));
	}


	public function addExportCsv(
		string $text,
		string $csvFileName,
		?string $outputEncoding = null,
		?string $delimiter = null,
		bool $includeBom = false
	): ExportCsv {
		return $this->addToExports(new ExportCsv(
			$this,
			$text,
			$csvFileName,
			false,
			$outputEncoding,
			$delimiter,
			$includeBom
		));
	}


	public function addExportCsvFiltered(
		string $text,
		string $csvFileName,
		?string $outputEncoding = null,
		?string $delimiter = null,
		bool $includeBom = false
	): ExportCsv {
		return $this->addToExports(new ExportCsv(
			$this,
			$text,
			$csvFileName,
			true,
			$outputEncoding,
			$delimiter,
			$includeBom
		));
	}


	protected function addToExports(Export $export): Export
	{
		$id = ($s = sizeof($this->exports)) ? ($s + 1) : 1;

		$export->setLink(new Link($this, 'export!', ['id' => $id]));

		return $this->exports[$id] = $export;
	}


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
	 * @throws DataGridException
	 */
	public function addToolbarButton(
		string $href,
		string $text = '',
		array $params = []
	): ToolbarButton
	{
		if (isset($this->toolbarButtons[$href])) {
			throw new DataGridException(
				sprintf('There is already toolbar button at key [%s] defined.', $href)
			);
		}

		return $this->toolbarButtons[$href] = new ToolbarButton($this, $href, $text, $params);
	}


	/**
	 * @throws DataGridException
	 */
	public function getToolbarButton(string $key): ToolbarButton
	{
		if (!isset($this->toolbarButtons[$key])) {
			throw new DataGridException(
				sprintf('There is no toolbar button at key [%s] defined.', $key)
			);
		}

		return $this->toolbarButtons[$key];
	}


	public function removeToolbarButton(string $key): self
	{
		unset($this->toolbarButtons[$key]);

		return $this;
	}


	/********************************************************************************
	 *                                 GROUP ACTIONS                                *
	 ********************************************************************************/


	public function addGroupAction(string $title, array $options = []): GroupAction
	{
		return $this->getGroupActionCollection()->addGroupSelectAction($title, $options);
	}


	public function addGroupSelectAction(string $title, array $options = []): GroupAction
	{
		return $this->getGroupActionCollection()->addGroupSelectAction($title, $options);
	}


	public function addGroupMultiSelectAction(string $title, array $options = []): GroupAction
	{
		return $this->getGroupActionCollection()->addGroupMultiSelectAction($title, $options);
	}


	public function addGroupTextAction(string $title): GroupAction
	{
		return $this->getGroupActionCollection()->addGroupTextAction($title);
	}


	public function addGroupTextareaAction(string $title): GroupAction
	{
		return $this->getGroupActionCollection()->addGroupTextareaAction($title);
	}


	public function getGroupActionCollection(): GroupActionCollection
	{
		if (!$this->groupActionCollection) {
			$this->groupActionCollection = new GroupActionCollection($this);
		}

		return $this->groupActionCollection;
	}


	public function hasGroupActions(): bool
	{
		return (bool) $this->groupActionCollection;
	}


	public function shouldShowSelectedRowsCount(): bool
	{
		return $this->showSelectedRowsCount;
	}


	public function setShowSelectedRowsCount(bool $show = true): self
	{
		$this->showSelectedRowsCount = $show;

		return $this;
	}


	/********************************************************************************
	 *                                   HANDLERS                                   *
	 ********************************************************************************/


	public function handlePage(int $page): void
	{
		$this->page = $page;
		$this->saveSessionData('_grid_page', $page);

		$this->reload(['table']);
	}


	/**
	 * @throws DataGridColumnNotFoundException
	 */
	public function handleSort(array $sort): void
	{
		if (count($sort) === 0) {
			$sort = $this->defaultSort;
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
				$this->sortCallback = $column->getSortableCallback();
			}
		}

		$this->saveSessionData('_grid_has_sorted', 1);
		$this->saveSessionData('_grid_sort', $this->sort = $sort);

		$this->reloadTheWholeGrid();
	}


	public function handleResetFilter(): void
	{
		/**
		 * Session stuff
		 */
		$this->deleteSessionData('_grid_page');

		if ($this->defaultFilterUseOnReset) {
			$this->deleteSessionData('_grid_has_filtered');
		}

		if ($this->defaultSortUseOnReset) {
			$this->deleteSessionData('_grid_has_sorted');
		}

		foreach ($this->getSessionData() as $key => $value) {
			if (!in_array($key, [
				'_grid_perPage',
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


	public function handleResetColumnFilter(string $key): void
	{
		$this->deleteSessionData($key);
		unset($this->filter[$key]);

		$this->reloadTheWholeGrid();
	}


	public function setColumnReset(bool $reset = true): self
	{
		$this->hasColumnReset = (bool) $reset;

		return $this;
	}


	public function hasColumnReset(): bool
	{
		return $this->hasColumnReset;
	}


	/**
	 * @param array|Filter[] $filters
	 */
	public function sendNonEmptyFiltersInPayload(array $filters): void
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
	 * @param mixed $id
	 */
	public function handleExport($id): void
	{
		if (!isset($this->exports[$id])) {
			throw new Nette\Application\ForbiddenRequestException;
		}

		if (!empty($this->columnsExportOrder)) {
			$this->setColumnsOrder($this->columnsExportOrder);
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
			$sort = [$this->primaryKey => 'ASC'];
			$filter = [];
		}

		if ($this->dataModel === null) {
			throw new DataGridException('You have to set a data source first.');
		}

		$rows = [];

		$items = Nette\Utils\Callback::invokeArgs(
			[$this->dataModel, 'filterData'], [
				null,
				$this->createSorting($this->sort, $this->sortCallback),
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
	 * @param mixed $parent
	 */
	public function handleGetChildren($parent): void
	{
		$this->setDataSource(
			call_user_func($this->treeViewChildrenCallback, $parent)
		);

		if ($this->getPresenter()->isAjax()) {
			$this->getPresenter()->payload->_datagrid_url = $this->refreshURL;
			$this->getPresenter()->payload->_datagrid_tree = $parent;

			$this->redrawControl('items');

			$this->onRedraw();
		} else {
			$this->getPresenter()->redirect('this');
		}
	}


	/**
	 * @param mixed $id
	 */
	public function handleGetItemDetail($id): void
	{
		$this->getTemplate()->add('toggle_detail', $id);
		$this->redrawItem = [$this->itemsDetail->getPrimaryWhereColumn() => $id];

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
	 * @param  mixed $id
	 * @param  mixed $key
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
	 * @param array|string[] $snippets
	 */
	public function reload(array $snippets = []): void
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

			$this->getPresenter()->payload->_datagrid_url = $this->refreshURL;
			$this->getPresenter()->payload->_datagrid_name = $this->getName();

			$this->onRedraw();
		} else {
			$this->getPresenter()->redirect('this');
		}
	}


	public function reloadTheWholeGrid(): void
	{
		if ($this->getPresenter()->isAjax()) {
			$this->redrawControl('grid');

			$this->getPresenter()->payload->_datagrid_url = $this->refreshURL;
			$this->getPresenter()->payload->_datagrid_name = $this->getName();

			$this->onRedraw();
		} else {
			$this->getPresenter()->redirect('this');
		}
	}


	public function handleChangeStatus(string $id, string $key, string $value): void
	{
		if (empty($this->columns[$key])) {
			throw new DataGridException(sprintf('ColumnStatus[%s] does not exist', $key));
		}

		$this->columns[$key]->onChange($id, $value);
	}


	/**
	 * @param mixed $primaryWhereColumn
	 */
	public function redrawItem(int $id, $primaryWhereColumn = null): void
	{
		$this->snippetsSet = true;

		$this->redrawItem = [($primaryWhereColumn ?: $this->primaryKey) => $id];

		$this->redrawControl('items');

		$this->getPresenter()->payload->_datagrid_url = $this->refreshURL;

		$this->onRedraw();
	}


	public function handleShowAllColumns(): void
	{
		$this->deleteSessionData('_grid_hidden_columns');
		$this->saveSessionData('_grid_hidden_columns_manipulated', true);

		$this->redrawControl();

		$this->onRedraw();
	}


	public function handleShowDefaultColumns(): void
	{
		$this->deleteSessionData('_grid_hidden_columns');
		$this->saveSessionData('_grid_hidden_columns_manipulated', false);

		$this->redrawControl();

		$this->onRedraw();
	}


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


	/**
	 * @param mixed $__key
	 * @param mixed $__id
	 */
	public function handleActionCallback($__key, $__id): void
	{
		$action = $this->getAction($__key);

		if (!($action instanceof ActionCallback)) {
			throw new DataGridException(
				sprintf('Action [%s] does not exist or is not an callback aciton.', $__key)
			);
		}

		$action->onClick($__id);
	}


	/********************************************************************************
	 *                                  PAGINATION                                  *
	 ********************************************************************************/


	/**
	 * @param array|int[]|string[]
	 */
	public function setItemsPerPageList(array $itemsPerPageList, bool $includeAll = true): self
	{
		$this->itemsPerPageList = $itemsPerPageList;

		if ($includeAll) {
			$this->itemsPerPageList[] = 'all';
		}

		return $this;
	}


	public function setDefaultPerPage(int $count): self
	{
		$this->defaultPerPage = $count;

		return $this;
	}


	/**
	 * User may set default "items per page" value, apply it
	 */
	public function findDefaultPerPage(): void
	{
		if (!empty($this->perPage)) {
			return;
		}

		if (!empty($this->defaultPerPage)) {
			$this->perPage = $this->defaultPerPage;
		}

		$this->saveSessionData('_grid_perPage', $this->perPage);
	}


	public function createComponentPaginator(): DataGridPaginator
	{
		$component = new Components\DataGridPaginator\DataGridPaginator(
			$this->getTranslator(),
			static::$iconPrefix
		);
		$paginator = $component->getPaginator();

		$paginator->setPage($this->page);
		$paginator->setItemsPerPage($this->getPerPage());

		if ($this->customPaginatorTemplate) {
			$component->setTemplateFile($this->customPaginatorTemplate);
		}

		return $component;
	}


	public function getPerPage(): int
	{
		$itemsPerPageList = $this->getItemsPerPageList();

		$perPage = $this->perPage ?: reset($itemsPerPageList);

		if (($perPage !== 'all' && !in_array((int) $this->perPage, $itemsPerPageList, true))
			|| ($perPage === 'all' && !in_array($this->perPage, $itemsPerPageList, true))) {
			$perPage = reset($itemsPerPageList);
		}

		return $perPage;
	}


	/**
	 * @return array|int[]|string[]
	 */
	public function getItemsPerPageList(): array
	{
		$list = array_flip($this->itemsPerPageList);

		foreach ($list as $key => $value) {
			$list[$key] = $key;
		}

		if (array_key_exists('all', $list)) {
			$list['all'] = $this->getTranslator()->translate('ublaboo_datagrid.all');
		}

		return $list;
	}


	public function setPagination(bool $doPaginate): self
	{
		$this->doPaginate = $doPaginate;

		return $this;
	}


	public function isPaginated(): bool
	{
		return $this->doPaginate;
	}


	public function getPaginator(): ?DataGridPaginator
	{
		if ($this->isPaginated() && $this->getPerPage() !== 'all') {
			return $this['paginator'];
		}

		return null;
	}


	/********************************************************************************
	 *                                     I18N                                     *
	 ********************************************************************************/


	public function setTranslator(ITranslator $translator): self
	{
		$this->translator = $translator;

		return $this;
	}


	public function getTranslator(): ITranslator
	{
		if (!$this->translator) {
			$this->translator = new SimpleTranslator;
		}

		return $this->translator;
	}


	/********************************************************************************
	 *                                 COLUMNS ORDER                                *
	 ********************************************************************************/


	/**
	 * Set order of datagrid columns
	 * @param array|string[] $order
	 */
	public function setColumnsOrder(array $order): self
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
	 * @param array|string[] $order
	 */
	public function setColumnsExportOrder(array $order): self
	{
		$this->columnsExportOrder = (array) $order;

		return $this;
	}


	/********************************************************************************
	 *                                SESSION & URL                                 *
	 ********************************************************************************/


	public function getSessionSectionName(): string
	{
		return $this->getPresenter()->getName() . ':' . $this->getUniqueId();
	}


	public function setRememberState(bool $remember = true): self
	{
		$this->rememberState = $remember;

		return $this;
	}


	public function setRefreshUrl(bool $refresh = true): self
	{
		$this->refreshURL = $refresh;


		return $this;
	}


	/**
	 * @return mixed
	 */
	public function getSessionData(?string $key = null, $defaultValue = null)
	{
		if (!$this->rememberState) {
			return $key ? $defaultValue : [];
		}

		return ($key ? $this->gridSession->{$key} : $this->gridSession) ?: $defaultValue;
	}


	/**
	 * @param  mixed $value
	 */
	public function saveSessionData(string $key, $value): void
	{
		if ($this->rememberState) {
			$this->gridSession->{$key} = $value;
		}
	}


	public function deleteSessionData(string $key): void
	{
		unset($this->gridSession->{$key});
	}


	/********************************************************************************
	 *                                  ITEM DETAIL                                 *
	 ********************************************************************************/


	/**
	 * Get items detail parameters
	 */
	public function getItemsDetail(): array
	{
		return $this->itemsDetail;
	}


	/**
	 * @param mixed $detail callable|string|bool
	 */
	public function setItemsDetail($detail = true, ?string $primaryWhereColumn = null): ItemDetail
	{
		if ($this->isSortable()) {
			throw new DataGridException('You can not use both sortable datagrid and items detail.');
		}

		$this->itemsDetail = new ItemDetail(
			$this,
			$primaryWhereColumn ?: $this->primaryKey
		);

		if (is_string($detail)) {
			/**
			 * Item detail will be in separate template
			 */
			$this->itemsDetail->setType('template');
			$this->itemsDetail->setTemplate($detail);

		} elseif (is_callable($detail)) {
			/**
			 * Item detail will be rendered via custom callback renderer
			 */
			$this->itemsDetail->setType('renderer');
			$this->itemsDetail->setRenderer($detail);

		} elseif ($detail === true) {
			/**
			 * Item detail will be rendered probably via block #detail
			 */
			$this->itemsDetail->setType('block');

		} else {
			throw new DataGridException(
				'::setItemsDetail() can be called either with no parameters or with parameter = template path or callable renderer.'
			);
		}

		return $this->itemsDetail;
	}


	public function setItemsDetailForm(callable $callableSetContainer): self
	{
		if ($this->itemsDetail instanceof ItemDetail) {
			$this->itemsDetail->setForm(
				new ItemDetailForm($callableSetContainer)
			);

			return $this;
		}

		throw new DataGridException('Please set the ItemDetail first.');
	}


	public function getItemDetailForm(): ?Container
	{
		if ($this->itemsDetail instanceof ItemDetail) {
			return $this->itemsDetail->getForm();
		}

		return null;
	}


	/********************************************************************************
	 *                                ROW PRIVILEGES                                *
	 ********************************************************************************/


	public function allowRowsGroupAction(callable $condition): void
	{
		$this->rowConditions['group_action'] = $condition;
	}


	public function allowRowsInlineEdit(callable $condition): void
	{
		$this->rowConditions['inline_edit'] = $condition;
	}


	public function allowRowsAction(string $key, callable $condition): void
	{
		$this->rowConditions['action'][$key] = $condition;
	}


	/**
	 * @throws DataGridException
	 */
	public function allowRowsMultiAction(
		string $multiActionKey,
		string $actionKey,
		callable $condition
	): void
	{
		if (!isset($this->actions[$multiActionKey])) {
			throw new DataGridException(
				sprintf('There is no action at key [%s] defined.', $multiActionKey)
			);
		}

		if (!$this->actions[$multiActionKey] instanceof Column\MultiAction) {
			throw new DataGridException(
				sprintf('Action at key [%s] is not a MultiAction.', $multiActionKey)
			);
		}

		$this->actions[$multiActionKey]->setRowCondition((string) $actionKey, $condition);
	}


	/**
	 * @return bool|callable
	 */
	public function getRowCondition(string $name, ?string $key = null)
	{
		if (!isset($this->rowConditions[$name])) {
			return false;
		}

		$condition = $this->rowConditions[$name];

		if ($key === null) {
			return $condition;
		}

		return $condition[$key] ?? false;
	}


	/********************************************************************************
	 *                               COLUMN CALLBACK                                *
	 ********************************************************************************/


	public function addColumnCallback(string $key, callable $callback): void
	{
		$this->columnCallbacks[$key] = $callback;
	}


	public function getColumnCallback(string $key): ?callable
	{
		return $this->columnCallbacks[$key] ?? null;
	}


	/********************************************************************************
	 *                                 INLINE EDIT                                  *
	 ********************************************************************************/


	public function addInlineEdit(?string $primaryWhereColumn = null): InlineEdit
	{
		$this->inlineEdit = new InlineEdit($this, $primaryWhereColumn ?? $this->primaryKey);

		return $this->inlineEdit;
	}


	public function getInlineEdit(): ?InlineEdit
	{
		return $this->inlineEdit;
	}


	/**
	 * @param mixed $id
	 */
	public function handleInlineEdit($id): void
	{
		if ($this->inlineEdit) {
			$this->inlineEdit->setItemId($id);

			$primaryWhereColumn = $this->inlineEdit->getPrimaryWhereColumn();

			$this['filter']['inline_edit']->addHidden('_id', $id);
			$this['filter']['inline_edit']->addHidden('_primary_where_column', $primaryWhereColumn);

			if ($this->getPresenter()->isAjax()) {
				$this->getPresenter()->payload->_datagrid_inline_editing = true;
				$this->getPresenter()->payload->_datagrid_name = $this->getName();
			}

			$this->redrawItem($id, $primaryWhereColumn);
		}
	}


	/********************************************************************************
	 *                                  INLINE ADD                                  *
	 ********************************************************************************/


	public function addInlineAdd(): InlineEdit
	{
		$this->inlineAdd = new InlineEdit($this);

		$this->inlineAdd
			->setTitle('ublaboo_datagrid.add')
			->setIcon('plus')
			->setClass('btn btn-xs btn-default btn-secondary');

		return $this->inlineAdd;
	}


	public function getInlineAdd(): ?InlineEdit
	{
		return $this->inlineAdd;
	}


	/********************************************************************************
	 *                               COLUMNS HIDING                                 *
	 ********************************************************************************/


	/**
	 * Can datagrid hide colums?
	 */
	public function canHideColumns(): bool
	{
		return $this->canHideColumns;
	}


	/**
	 * Order Grid to set columns hideable.
	 */
	public function setColumnsHideable(): self
	{
		$this->canHideColumns = true;

		return $this;
	}


	/********************************************************************************
	 *                                COLUMNS SUMMARY                               *
	 ********************************************************************************/


	public function hasColumnsSummary(): bool
	{
		return $this->columnsSummary instanceof ColumnsSummary;
	}


	/**
	 * @param array|string[] $columns
	 */
	public function setColumnsSummary(array $columns, ?callable $rowCallback = null): ColumnsSummary
	{
		if ($this->hasSomeAggregationFunction()) {
			throw new DataGridException('You can use either ColumnsSummary or AggregationFunctions');
		}

		if ($rowCallback !== null) {
			if (!is_callable($rowCallback)) {
				throw new \InvalidArgumentException('Row summary callback must be callable');
			}
		}

		$this->columnsSummary = new ColumnsSummary($this, $columns, $rowCallback);

		return $this->columnsSummary;
	}


	public function getColumnsSummary(): ?ColumnsSummary
	{
		return $this->columnsSummary;
	}


	/********************************************************************************
	 *                                   INTERNAL                                   *
	 ********************************************************************************/


	/**
	 * Tell grid filters to by submitted automatically
	 */
	public function setAutoSubmit(bool $autoSubmit = true): self
	{
		$this->autoSubmit = $autoSubmit;

		return $this;
	}


	public function hasAutoSubmit(): bool
	{
		return $this->autoSubmit;
	}


	public function getFilterSubmitButton(): SubmitButton
	{
		if ($this->hasAutoSubmit()) {
			throw new DataGridException(
				'DataGrid has auto-submit. Turn it off before setting filter submit button.'
			);
		}

		if ($this->filterSubmitButton === null) {
			$this->filterSubmitButton = new SubmitButton($this);
		}

		return $this->filterSubmitButton;
	}


	/********************************************************************************
	 *                                   INTERNAL                                   *
	 ********************************************************************************/


	/**
	 * @internal
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
	 * @internal
	 */
	public function getPrimaryKey(): string
	{
		return $this->primaryKey;
	}


	/**
	 * @return Column\Column[]
	 * @internal
	 */
	public function getColumns(): array
	{
		$return = $this->columns;

		try {
			$this->getParentComponent();

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
					$this->columnsVisibility[$column] = [
						'visible' => false,
					];

					unset($return[$column]);
				}
			}

		} catch (DataGridHasToBeAttachedToPresenterComponentException $e) {
		}

		return $return;
	}


	/**
	 * @internal
	 */
	public function getColumnsVisibility(): array
	{
		$return = $this->columnsVisibility;

		foreach ($this->columnsVisibility as $key => $column) {
			$return[$key]['column'] = $this->columns[$key];
		}

		return $return;
	}


	/**
	 * @internal
	 */
	public function getParentComponent(): Component
	{
		$parent = parent::getParent();

		if (!$parent instanceof Component) {
			throw new DataGridHasToBeAttachedToPresenterComponentException(
				sprintf(
					'DataGrid is attached to: "%s", but instance of %s is needed.',
					($parent ? get_class($parent) : 'null'),
					Component::class
				)
			);
		}

		return $parent;
	}


	/**
	 * @internal
	 */
	public function getSortableParentPath(): string
	{
		return $this->getParentComponent()->lookupPath(IPresenter::class, false);
	}


	/**
	 * Some of datagrid columns may be hidden by default
	 * @internal
	 */
	public function setSomeColumnDefaultHide(bool $defaultHide): self
	{
		$this->someColumnDefaultHide = $defaultHide;

		return $this;
	}


	/**
	 * Are some of columns hidden bydefault?
	 * @internal
	 */
	public function hasSomeColumnDefaultHide(): bool
	{
		return $this->someColumnDefaultHide;
	}


	/**
	 * Simply refresh url
	 * @internal
	 */
	public function handleRefreshState(): void
	{
		$this->findSessionValues();
		$this->findDefaultFilter();
		$this->findDefaultSort();
		$this->findDefaultPerPage();

		$this->getPresenter()->payload->_datagrid_url = $this->refreshURL;
		$this->redrawControl('non-existing-snippet');
	}


	/**
	 * @internal
	 */
	public function setCustomPaginatorTemplate(string $templateFile): void
	{
		$this->customPaginatorTemplate = $templateFile;
	}
}
