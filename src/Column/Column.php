<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Column;

use Nette\InvalidArgumentException;
use Ublaboo;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Row;
use Ublaboo\DataGrid\Exception\DataGridException;
use Ublaboo\DataGrid\Exception\DataGridColumnRendererException;
use Ublaboo\DataGrid\Exception\DataGridHasToBeAttachedToPresenterComponentException;
use Nette\Utils\Html;

abstract class Column extends FilterableColumn
{

	/**
	 * @var array
	 */
	protected $replacements = [];

	/**
	 * @var Renderer|NULL
	 */
	protected $renderer;

	/**
	 * @var string
	 */
	protected $template;

	/**
	 * @var bool|string
	 */
	protected $sortable = FALSE;

	/**
	 * @var bool
	 */
	protected $sortable_reset_pagination = FALSE;

	/**
	 * @var null|callable
	 */
	protected $sortable_callback = NULL;

	/**
	 * @var array
	 */
	protected $sort;

	/**
	 * @var bool
	 */
	protected $template_escaping = TRUE;

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
	 * Cached html elements
	 * @var array
	 */
	protected $el_cache = [];


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
	 * Try to render item with custom renderer
	 * @param  Row   $row
	 * @return mixed
	 */
	public function useRenderer(Row $row)
	{
		$renderer = $this->getRenderer();

		if (!$renderer) {
			throw new DataGridColumnRendererException;
		}

		if ($renderer->getConditionCallback()) {
			if (!call_user_func_array($renderer->getConditionCallback(), [$row->getItem()])) {
				throw new DataGridColumnRendererException;
			}

			return call_user_func_array($renderer->getCallback(), [$row->getItem()]);
		}

		return call_user_func_array($renderer->getCallback(), [$row->getItem()]);
	}


	/**
	 * Should be column values escaped in latte?
	 * @param bool $template_escaping
	 */
	public function setTemplateEscaping($template_escaping = TRUE)
	{
		$this->template_escaping = (bool) $template_escaping;

		return $this;
	}


	public function isTemplateEscaped()
	{
		return $this->template_escaping;
	}


	/**
	 * Set column sortable or not
	 * @param bool|string $sortable
	 */
	public function setSortable($sortable = TRUE)
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
	 * Shoud be the pagination reseted after sorting?
	 * @param bool $sortable_reset_pagination
	 * @return static
	 */
	public function setSortableResetPagination($sortable_reset_pagination = TRUE)
	{
		$this->sortable_reset_pagination = (bool) $sortable_reset_pagination;

		return $this;
	}


	/**
	 * DO reset pagination after sorting?
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
	 * Set column replacements
	 * @param  array $replacements
	 * @return Column
	 */
	public function setReplacement(array $replacements)
	{
		$this->replacements = $replacements;

		return $this;
	}


	/**
	 * Tell whether columns has replacements
	 * @return bool
	 */
	public function hasReplacements()
	{
		return (bool) $this->replacements;
	}


	/**
	 * Apply replacements
	 * @param  Row   $row
	 * @return array
	 */
	public function applyReplacements(Row $row)
	{
		$value = $row->getValue($this->column);

		if ((is_scalar($value) || is_null($value)) && isset($this->replacements[$value])) {
			return [TRUE, $this->replacements[$value]];
		}

		return [FALSE, NULL];
	}


	/**
	 * Set renderer callback and (it may be optional - the condition callback will decide)
	 * @param callable $renderer
	 */
	public function setRenderer($renderer, $condition_callback = NULL)
	{
		if ($this->hasReplacements()) {
			throw new DataGridException(
				"Use either Column::setReplacement() or Column::setRenderer, not both."
			);
		}

		if (!is_callable($renderer)) {
			throw new DataGridException(
				"Renderer (method Column::setRenderer()) must be callable."
			);
		}

		if (NULL != $condition_callback && !is_callable($condition_callback)) {
			throw new DataGridException(
				"Renderer (method Column::setRenderer()) must be callable."
			);
		}

		$this->renderer = new Renderer($renderer, $condition_callback);

		return $this;
	}


	/**
	 * Set renderer callback just if condition is truthy
	 * @param callable $renderer
	 */
	public function setRendererOnCondition($renderer, $condition_callback)
	{
		return $this->setRenderer($renderer, $condition_callback);
	}


	/**
	 * Return custom renderer callback
	 * @return Renderer|null
	 */
	public function getRenderer()
	{
		return $this->renderer;
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
		$this->sort = $sort[$this->getSortingColumn()];

		return $this;
	}


	/**
	 * What sorting will be applied after next click?
	 * @return array
	 */
	public function getSortNext()
	{
		if ($this->sort == 'ASC') {
			return [$this->key => 'DESC'];
		} else if ($this->sort == 'DESC') {
			return [$this->key => NULL];
		}

		return [$this->key => 'ASC'];
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
	 * @param  string   $tag th|td
	 * @param  string   $key
	 * @param  Row|NULL $row
	 * @return Html
	 */
	public function getElementPrototype($tag, $key = NULL, Row $row = NULL)
	{
		/**
		 * Get cached element
		 */
		if (empty($this->el_cache[$tag])) {
			$this->el_cache[$tag] = $el = $el = Html::el($tag);
		} else {
			$el = $this->el_cache[$tag];
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

		/**
		 * Method called from datagrid template, set appropriate classes and another attributes
		 */
		if ($key !== NULL && $row !== NULL) {
			$el->class[] = "col-{$key}";

			if ($tag == 'td') {
				if ($this->isEditable()) {
					$link = $this->grid->link('edit!', ['key' => $key, 'id' => $row->getId()]);

					$el->data('datagrid-editable-url', $link);
				}
			}
		}

		return $el;
	}


	/**
	 * Create link to custom destination
	 * @param  string $href
	 * @param  array  $params
	 * @return string
	 * @throws DataGridHasToBeAttachedToPresenterComponentException
	 * @throws InvalidArgumentException
	 */
	protected function createLink($href, $params)
	{
		try {
			$parent = $this->grid->getParent();

			return $parent->link($href, $params);
		} catch (DataGridHasToBeAttachedToPresenterComponentException $e) {
			$parent = $this->grid->getPresenter();

		} catch (InvalidArgumentException $e) {
			$parent = $this->grid->getPresenter();

		}

		return $parent->link($href, $params);
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

}
