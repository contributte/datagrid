<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Column;

use Nette\Utils\Html;
use Ublaboo\DataGrid\Exception\DataGridColumnRendererException;
use Ublaboo\DataGrid\Row;

abstract class Column extends FilterableColumn
{

	use TButtonRenderer;
	use TLink;

	/** @var string|null */
	protected $template;

	/** @var bool|string */
	protected $sortable = false;

	/** @var bool */
	protected $translatableHeader = true;

	/** @var bool */
	protected $sortableResetPagination = false;

	/** @var callable|null */
	protected $sortableCallback = null;

	/** @var array */
	protected $sort;

	/** @var bool */
	protected $templateEscaping = true;

	/** @var bool */
	protected $headerEscaping = false;

	/** @var string */
	protected $align;

	/** @var array */
	protected $templateVariables = [];

	/** @var callable */
	protected $editableCallback;

	/** @var callable|null */
	protected $editableConditionCallback = null;

	/** @var array */
	protected $editableElement = ['textarea', ['class' => 'form-control']];

	/** @var bool */
	protected $defaultHide = false;

	/** @var array */
	protected $elementCache = ['td' => null, 'th' => null];

	/** @var callable|null */
	protected $editableValueCallback = null;

	/**
	 * @return mixed
	 */
	public function render(Row $row)
	{
		try {
			return $this->useRenderer($row);
		} catch (DataGridColumnRendererException $e) {
			/**
			 * Do not use renderer
			 */
		}

		/**
		 * Or replacements may be applied
		 */
		[$replace, $replaced] = $this->applyReplacements($row, $this->column);

		if ($replace) {
			return $replaced;
		}

		return $this->getColumnValue($row);
	}


	/**
	 * Should be column values escaped in latte?
	 */
	public function setTemplateEscaping(bool $templateEscaping = true): self
	{
		$this->templateEscaping = (bool) $templateEscaping;

		return $this;
	}


	public function isTemplateEscaped(): bool
	{
		return $this->templateEscaping;
	}


	/**
	 * Should be column header escaped in latte?
	 */
	public function setHeaderEscaping(bool $headerEscaping = false): self
	{
		$this->headerEscaping = $headerEscaping;

		return $this;
	}


	public function isHeaderEscaped(): bool
	{
		return $this->headerEscaping;
	}


	/**
	 * Set column sortable or not
     *
     * @param bool|string $sortable
	 */
	public function setSortable($sortable = true): self
	{
		$this->sortable = is_string($sortable)
            ? $sortable
            : (bool) $sortable;

		return $this;
	}


	/**
	 * Tell whether column is sortable
	 */
	public function isSortable(): bool
	{
		return $this->sortable;
	}


	/**
	 * Set column translatable or not
	 */
	public function setTranslatableHeader(bool $translatableHeader = true): self
	{
		$this->translatableHeader = $translatableHeader;

		return $this;
	}


	/**
	 * Tell wheter column is translatable
	 */
	public function isTranslatableHeader(): bool
	{
		return $this->translatableHeader;
	}


	/**
	 * Shoud be the pagination reseted after sorting?
	 */
	public function setSortableResetPagination(bool $sortableResetPagination = true): self
	{
		$this->sortableResetPagination = $sortableResetPagination;

		return $this;
	}


	/**
	 * Do reset pagination after sorting?
	 */
	public function sortableResetPagination(): bool
	{
		return $this->sortableResetPagination;
	}


	/**
	 * Set custom ORDER BY clause
	 */
	public function setSortableCallback(callable $sortableCallback): self
	{
		$this->sortableCallback = $sortableCallback;

		return $this;
	}


	/**
	 * Get custom ORDER BY clause
	 */
	public function getSortableCallback(): ?callable
	{
		return $this->sortableCallback;
	}


	public function getSortingColumn(): string
	{
		return is_string($this->sortable)
            ? $this->sortable
            : $this->column;
	}


	public function getColumnName(): string
	{
		return $this->column;
	}


	/**
	 * @return mixed
	 */
	public function getColumnValue(Row $row)
	{
		return $row->getValue($this->column);
	}


	public function getName(): string
	{
		return $this->name;
	}


	/**
	 * Column may have its own template
	 */
	public function setTemplate(string $template, array $templateVariables = []): self
	{
		$this->template = $template;
		$this->templateVariables = $templateVariables;

		return $this;
	}


	/**
	 * Column can have variables that will be passed to custom template scope
	 */
	public function getTemplateVariables(): array
	{
		return $this->templateVariables;
	}


	/**
	 * Tell whether column has its owntemplate
	 */
	public function hasTemplate(): bool
	{
		return $this->template !== null;
	}


	/**
	 * Get column template path
	 */
	public function getTemplate(): string
	{
		return $this->template;
	}


	/**
	 * Tell whether data source is sorted by this column
	 */
	public function isSortedBy(): bool
	{
		return (bool) $this->sort;
	}


	/**
	 * Tell column his sorting options
	 */
	public function setSort(array $sort): self
	{
		$this->sort = $sort[$this->key];

		return $this;
	}


	/**
	 * What sorting will be applied after next click?
	 */
	public function getSortNext(): array
	{
		$defaultSort = $this->grid->getColumnDefaultSort($this->key);

		if ($this->sort === 'ASC') {
			return [$this->key => $defaultSort === 'DESC' ? false : 'DESC'];
		} elseif ($this->sort === 'DESC') {
			return [$this->key => $defaultSort === 'DESC' ? 'ASC' : false];
		}

		return [$this->key => 'ASC'];
	}


	public function hasSortNext(): bool
	{
		foreach ($this->getSortNext() as $order) {
			return $order !== false;
		}

		return false;
	}


	public function isSortAsc(): bool
	{
		return $this->sort === 'ASC';
	}


	/**
	 * Set column alignment
	 */
	public function setAlign(string $align): self
	{
		$this->align = (string) $align;

		return $this;
	}


	/**
	 * Has column some alignment?
	 */
	public function hasAlign(): bool
	{
		return (bool) $this->align;
	}


	/**
	 * Get column alignment
	 */
	public function getAlign(): string
	{
		return $this->align ?: 'left';
	}


	public function setFitContent(bool $fitContent = true): self
	{
		$fitContent
            ? $this->addAttributes(['class' => 'datagrid-fit-content'])
            : null;

		return $this;
	}


	/**
	 * Set callback that will be called after inline editing
	 */
	public function setEditableCallback(callable $editableCallback): self
	{
		$this->editableCallback = $editableCallback;

		return $this;
	}


	/**
	 * Return callback that is used after inline editing
	 */
	public function getEditableCallback(): callable
	{
		return $this->editableCallback;
	}


	/**
	 * Set inline editing just if condition is truthy
	 */
	public function setEditableOnConditionCallback(callable $editableConditionCallback): self
	{
		$this->editableConditionCallback = $editableConditionCallback;

		return $this;
	}


	public function getEditableOnConditionCallback(): ?callable
	{
		return $this->editableConditionCallback;
	}


	public function isEditable(?Row $row = null): bool
	{
		return ((bool) $this->getEditableCallback())
			&& ($row === null || $this->getEditableOnConditionCallback() === null || call_user_func_array($this->getEditableOnConditionCallback(), [$row->getItem()]));
	}


	/**
	 * Element is by default textarea, user can change that
	 */
	public function setEditableInputType(string $elType, array $attrs = []): self
	{
		$this->editableElement = [$elType, $attrs];

		return $this;
	}


	/**
	 * Change small inline edit input type to select
	 */
	public function setEditableInputTypeSelect(array $options = [], array $attrs = []): self
	{
		$select = Html::el('select');

		foreach ($options as $value => $text) {
			$select->create('option')
				->value($value)
				->setText($text);
		}

		$this->addAttributes(['data-datagrid-editable-element' => (string) $select]);

		return $this->setEditableInputType('select', $attrs);
	}


	public function setEditableValueCallback(callable $editableValueCallback): self
	{
		$this->editableValueCallback = $editableValueCallback;

		return $this;
	}


	public function getEditableValueCallback(): ?callable
	{
		return $this->editableValueCallback;
	}


	public function getEditableInputType(): array
	{
		return $this->editableElement;
	}


	/**
	 * Set attributes for both th and td element
	 */
	public function addCellAttributes(array $attrs): self
	{
		$this->getElementPrototype('td')->addAttributes($attrs);
		$this->getElementPrototype('th')->addAttributes($attrs);

		return $this;
	}


	/**
	 * Get th/td column element
	 */
	public function getElementPrototype(string $tag): Html
	{
		if ($this->elementCache[$tag]) {
			return $this->elementCache[$tag];
		}

		return $this->elementCache[$tag] = Html::el($tag);
	}


	/**
	 * Method called from datagrid template, set appropriate classes and another attributes
	 */
	public function getElementForRender(string $tag, string $key, ?Row $row = null): Html
	{
		$el = $this->elementCache[$tag]
            ? clone $this->elementCache[$tag]
            : Html::el($tag);

		/**
		 * If class was set by user via $el->class = '', fix it
		 */
		if (!empty($el->class) && is_string($el->class)) {
			$class = $el->class;
			unset($el->class);

			$el->class[] = $class;
		}

		$el->class[] = sprintf('text-%s', $this->getAlign());
		$el->class[] = sprintf('col-%s', $key);

		if ($row && $tag === 'td' && $this->isEditable($row)) {
			$link = $this->grid->link('edit!', ['key' => $key, 'id' => $row->getId()]);

			$el->data('datagrid-editable-url', $link);

			$el->data('datagrid-editable-type', $this->editableElement[0]);
			$el->data('datagrid-editable-attrs', json_encode($this->editableElement[1]));

			if ($this->getEditableValueCallback()) {
				$el->data(
					'datagrid-editable-value',
					call_user_func_array($this->getEditableValueCallback(), [$row->getItem()])
				);
			}
		}

		return $el;
	}


	public function setDefaultHide(bool $defaultHide = true): self
	{
		$this->defaultHide = $defaultHide;

		if ($defaultHide) {
			$this->grid->setSomeColumnDefaultHide($defaultHide);
		}

		return $this;
	}


	public function getDefaultHide(): bool
	{
		return $this->defaultHide;
	}


	/**
	 * Get row item params (E.g. action may be called id => $item->id, name => $item->name, ...)
	 */
	protected function getItemParams(Row $row, array $paramsList): array
	{
		$return = [];

		foreach ($paramsList as $paramName => $param) {
			$return[is_string($paramName) ? $paramName : $param] = $row->getValue($param);
		}

		return $return;
	}


	public function getColumn(): string
	{
		return $this->column;
	}

}
