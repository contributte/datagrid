<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Column;

use Nette\Utils\Html;
use Ublaboo;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridColumnRendererException;
use Ublaboo\DataGrid\Exception\DataGridException;
use Ublaboo\DataGrid\Row;
use Ublaboo\DataGrid\Traits;

abstract class Column extends FilterableColumn
{
	use Traits\TButtonRenderer;
	use Traits\TLink;

	/**
	 * @var string
	 */
	protected $template;

	/**
	 * @var bool|string
	 */
	protected $sortable = false;

	/**
	 * @var bool
	 */
	protected $translatable_header = true;

	/**
	 * @var bool
	 */
	protected $sortable_reset_pagination = false;

	/**
	 * @var null|callable
	 */
	protected $sortable_callback = null;

	/**
	 * @var array
	 */
	protected $sort;

	/**
	 * @var bool
	 */
	protected $template_escaping = true;

	/**
	 * @var bool
	 */
	protected $header_escaping = false;


	/**
	 * @var string
	 */
	protected $align;


	/**
	 * @var array
	 */
	protected $template_variables = [];

	/**
	 * @var callable
	 */
	protected $editable_callback;

	/**
	 * @var array
	 */
	protected $editable_element = ['textarea', ['class' => 'form-control']];

	/**
	 * @var bool
	 */
	protected $default_hide = false;

	/**
	 * @var array
	 */
	protected $elementCache = ['td' => null, 'th' => null];

	/**
	 * @var callable|NULL
	 */
	protected $editable_value_callback = null;


	/**
	 * Render row item into template
	 * @param  Row   $row
	 * @return mixed
	 */
	public function render(Row $row)
	{
		/**
		 * Renderer function may be used
		 */
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
		list($do_replace, $replaced) = $this->applyReplacements($row);

		if ($do_replace) {
			return $replaced;
		}

		return $this->getColumnValue($row);
	}


	/**
	 * Should be column values escaped in latte?
	 * @param bool $template_escaping
	 */
	public function setTemplateEscaping($template_escaping = true)
	{
		$this->template_escaping = (bool) $template_escaping;

		return $this;
	}


	public function isTemplateEscaped()
	{
		return $this->template_escaping;
	}


	/**
	 * Should be column header escaped in latte?
	 * @param bool $header_escaping
	 */
	public function setHeaderEscaping($header_escaping = false)
	{
		$this->header_escaping = (bool) $header_escaping;

		return $this;
	}


	public function isHeaderEscaped()
	{
		return $this->header_escaping;
	}


	/**
	 * Set column sortable or not
	 * @param bool|string $sortable
	 */
	public function setSortable($sortable = true)
	{
		$this->sortable = is_string($sortable) ? $sortable : (bool) $sortable;

		return $this;
	}


	/**
	 * Tell whether column is sortable
	 * @return bool
	 */
	public function isSortable()
	{
		return (bool) $this->sortable;
	}


	/**
	 * Set column translatable or not
	 */
	public function setTranslatableHeader($translatable_header = true)
	{
		$this->translatable_header = (bool) $translatable_header;

		return $this;
	}


	/**
	 * Tell wheter column is translatable
	 */
	public function isTranslatableHeader()
	{
		return (bool) $this->translatable_header;
	}


	/**
	 * Shoud be the pagination reseted after sorting?
	 * @param bool $sortable_reset_pagination
	 * @return static
	 */
	public function setSortableResetPagination($sortable_reset_pagination = true)
	{
		$this->sortable_reset_pagination = (bool) $sortable_reset_pagination;

		return $this;
	}


	/**
	 * Do reset pagination after sorting?
	 * @return bool
	 */
	public function sortableResetPagination()
	{
		return $this->sortable_reset_pagination;
	}


	/**
	 * Set custom ORDER BY clause
	 * @param callable $sortable_callback
	 * @return static
	 */
	public function setSortableCallback(callable $sortable_callback)
	{
		$this->sortable_callback = $sortable_callback;

		return $this;
	}


	/**
	 * Get custom ORDER BY clause
	 * @return callable|null
	 */
	public function getSortableCallback()
	{
		return $this->sortable_callback;
	}


	/**
	 * Get column to sort by
	 * @return string
	 */
	public function getSortingColumn()
	{
		return is_string($this->sortable) ? $this->sortable : $this->column;
	}


	/**
	 * Get column name
	 * @return string
	 */
	public function getColumnName()
	{
		return $this->column;
	}


	/**
	 * Get column value of row item
	 * @param  Row   $row
	 * @return mixed
	 */
	public function getColumnValue(Row $row)
	{
		return $row->getValue($this->column);
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * Column may have its own template
	 * @param string $template
	 */
	public function setTemplate($template, array $template_variables = [])
	{
		$this->template = $template;
		$this->template_variables = $template_variables;

		return $this;
	}


	/**
	 * Column can have variables that will be passed to custom template scope
	 * @return array
	 */
	public function getTemplateVariables()
	{
		return $this->template_variables;
	}


	/**
	 * Tell whether column has its owntemplate
	 * @return bool
	 */
	public function hasTemplate()
	{
		return (bool) $this->template;
	}


	/**
	 * Get column template path
	 * @return string
	 */
	public function getTemplate()
	{
		return $this->template;
	}


	/**
	 * Tell whether data source is sorted by this collumn
	 * @return bool
	 */
	public function isSortedBy()
	{
		return (bool) $this->sort;
	}


	/**
	 * Tell column his sorting options
	 * @param array $sort
	 */
	public function setSort(array $sort)
	{
		$this->sort = $sort[$this->key];

		return $this;
	}


	/**
	 * What sorting will be applied after next click?
	 * @return array
	 */
	public function getSortNext()
	{
		$defaultSort = $this->grid->getColumnDefaultSort($this->key);

		if ($this->sort == 'ASC') {
			return [$this->key => $defaultSort === 'DESC' ? FALSE : 'DESC'];
		} else if ($this->sort == 'DESC') {
			return [$this->key => $defaultSort === 'DESC' ? 'ASC' : FALSE];
		}

		return [$this->key => 'ASC'];
	}


	/**
	 * @return bool
	 */
	public function hasSortNext()
	{
		foreach ($this->getSortNext() as $key => $order) {
			return $order !== false;
		}
	}


	/**
	 * Is sorting ascending?
	 * @return bool
	 */
	public function isSortAsc()
	{
		return $this->sort == 'ASC';
	}


	/**
	 * Set column alignment
	 * @param string $align
	 */
	public function setAlign($align)
	{
		$this->align = (string) $align;

		return $this;
	}


	/**
	 * Has column some alignment?
	 * @return bool [description]
	 */
	public function hasAlign()
	{
		return (bool) $this->align;
	}


	/**
	 * Get column alignment
	 * @return string
	 */
	public function getAlign()
	{
		return $this->align ?: 'left';
	}


	/**
	 * Set column content fit
	 * @param bool $fit_content
	 * @return $this
	 */
	public function setFitContent($fit_content = true)
	{
		($fit_content) ? $this->addAttributes(['class' => 'datagrid-fit-content']) : null;

		return $this;
	}


	/**
	 * Set callback that will be called after inline editing
	 * @param callable $editable_callback
	 */
	public function setEditableCallback(callable $editable_callback)
	{
		$this->editable_callback = $editable_callback;

		return $this;
	}


	/**
	 * Return callback that is used after inline editing
	 * @return callable
	 */
	public function getEditableCallback()
	{
		return $this->editable_callback;
	}


	/**
	 * Is column editable?
	 * @return bool
	 */
	public function isEditable()
	{
		return (bool) $this->getEditableCallback();
	}


	/**
	 * Element is by default textarea, user can change that
	 * @param string $el_type
	 * @param array  $attrs
	 * @return static
	 */
	public function setEditableInputType($el_type, array $attrs = [])
	{
		$this->editable_element = [$el_type, $attrs];

		return $this;
	}


	/**
	 * Change small inline edit input type to select
	 * @param array  $options
	 * @param array  $attrs
	 * @return static
	 */
	public function setEditableInputTypeSelect(array $options = [], array $attrs = [])
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


	/**
	 * @param callable $editable_value_callback
	 * @return static
	 */
	public function setEditableValueCallback(callable $editable_value_callback)
	{
		$this->editable_value_callback = $editable_value_callback;

		return $this;
	}


	/**
	 * @return callable|NULL
	 */
	public function getEditableValueCallback()
	{
		return $this->editable_value_callback;
	}


	/**
	 * @return array
	 */
	public function getEditableInputType()
	{
		return $this->editable_element;
	}


	/**
	 * Set attributes for both th and td element
	 * @param array $attrs
	 * @return static
	 */
	public function addAttributes(array $attrs)
	{
		$this->getElementPrototype('td')->addAttributes($attrs);
		$this->getElementPrototype('th')->addAttributes($attrs);

		return $this;
	}


	/**
	 * Get th/td column element
	 * @param  string $tag th|td
	 * @return Html
	 */
	public function getElementPrototype($tag)
	{
		if ($this->elementCache[$tag]) {
			return $this->elementCache[$tag];
		}

		return $this->elementCache[$tag] = Html::el($tag);
	}


	/**
	 * Method called from datagrid template, set appropriate classes and another attributes
	 * @param  string   $tag
	 * @param  string   $key
	 * @param  Row|NULL $row
	 * @return Html
	 */
	public function getElementForRender($tag, $key, Row $row = null)
	{
		if ($this->elementCache[$tag]) {
			$el = clone $this->elementCache[$tag];
		} else {
			$el = Html::el($tag);
		}

		/**
		 * If class was set by user via $el->class = '', fix it
		 */
		if (!empty($el->class) && is_string($el->class)) {
			$class = $el->class;
			unset($el->class);

			$el->class[] = $class;
		}

		$el->class[] = "text-{$this->getAlign()}";
		$el->class[] = "col-{$key}";

		if ($row && $tag == 'td' && $this->isEditable()) {
			$link = $this->grid->link('edit!', ['key' => $key, 'id' => $row->getId()]);

			$el->data('datagrid-editable-url', $link);

			$el->data('datagrid-editable-type', $this->editable_element[0]);
			$el->data('datagrid-editable-attrs', json_encode($this->editable_element[1]));

			if ($this->getEditableValueCallback()) {
				$el->data(
					'datagrid-editable-value',
					call_user_func_array($this->getEditableValueCallback(), [$row->getItem()])
				);
			}
		}

		return $el;
	}


	/**
	 * @param bool $default_hide
	 * @return static
	 */
	public function setDefaultHide($default_hide = true)
	{
		$this->default_hide = (bool) $default_hide;

		if ($default_hide) {
			$this->grid->setSomeColumnDefaultHide($default_hide);
		}

		return $this;
	}


	public function getDefaultHide()
	{
		return $this->default_hide;
	}


	/**
	 * Get row item params (E.g. action may be called id => $item->id, name => $item->name, ...)
	 * @param  Row   $row
	 * @param  array $params_list
	 * @return array
	 */
	protected function getItemParams(Row $row, array $params_list)
	{
		$return = [];

		foreach ($params_list as $param_name => $param) {
			$return[is_string($param_name) ? $param_name : $param] = $row->getValue($param);
		}

		return $return;
	}


	/**
	 * @return string
	 */
	public function getColumn()
	{
		return $this->column;
	}
}
