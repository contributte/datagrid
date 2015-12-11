<?php

/**
 * * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Column;

use Ublaboo\DataGrid\DataGridException,
	Nette;

class Column extends Nette\Object
{

	/**
	 * @var string
	 */
	protected $column;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var array
	 */
	protected $replacements = [];

	/**
	 * @var callable
	 */
	protected $renderer;

	/**
	 * @var string
	 */
	protected $template;

	/**
	 * @var boolean
	 */
	protected $is_sortable = FALSE;

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


	public function __construct($column, $name)
	{
		$this->column = $column;
		$this->name = $name;
	}


	public function render($item)
	{
		/**
		 * Renderer function may be used
		 */
		if ($renderer = $this->getRenderer()) {
			return call_user_func_array($renderer, [$item]);
		}

		/**
		 * Or replacements may be applied
		 */
		list($do_replace, $replaced) = $this->applyReplacements($item);
		if ($do_replace) {
			return $replaced;
		}

		return $this->getColumnValue($item);
	}


	/**
	 * Should be column values escaped in latte?
	 * @param boolean $template_escaping
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
	 * @param bool $sortable
	 */
	public function setSortable($sortable = TRUE)
	{
		$this->is_sortable = (bool) $sortable;

		return $this;
	}


	/**
	 * Tell whether column is sortable
	 * @return boolean
	 */
	public function isSortable()
	{
		return $this->is_sortable;
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
	 * Get column value of item
	 * @param  mixed $item
	 * @return mixed
	 */
	public function getColumnValue($item)
	{
		if (is_object($item)) {
			return $item->{$this->column};
		}

		return $item[$this->column];
	}


	public function getName()
	{
		return $this->name;
	}


	/**
	 * Set column replacements
	 * @param  array $replacements
	 * @return void
	 */
	public function setReplacement(array $replacements)
	{
		$this->replacements = $replacements;

		return $this;
	}


	/**
	 * Tell whether columns has replacements
	 * @return boolean
	 */
	public function hasReplacements()
	{
		return (bool) $this->replacements;
	}


	/**
	 * apply replacements
	 * @param  mixed $item
	 * @return array
	 */
	public function applyReplacements($item)
	{
		if (is_object($item)) {
			$value = $item->{$this->column};
		} else {
			$value = $item[$this->column];
		}

		if ((is_scalar($value) || is_null($value)) && isset($this->replacements[$value])) {
			return [TRUE, $this->replacements[$value]];
		}

		return [FALSE, NULL];
	}


	/**
	 * Set renderer callback
	 * @param callable $renderer
	 */
	public function setRenderer($renderer)
	{
		if ($this->hasReplacements()) {
			throw new DataGridException (
				"Use either Column::setReplacement() or Column::setRenderer, not both."
			);
		}

		if (!is_callable($renderer)) {
			throw new DataGridException (
				"Renderer (method Column::setRenderer()) must be callable."
			);
		}

		$this->renderer = $renderer;

		return $this;
	}


	/**
	 * Return custom renderer callback
	 * @return callable
	 */
	public function getRenderer()
	{
		return $this->renderer;
	}


	/**
	 * Column may have its own template
	 * @param string $template
	 */
	public function setTemplate($template)
	{
		$this->template = $template;

		return $this;
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
	 * @return boolean
	 */
	public function isSortedBy()
	{
		return (bool) $this->sort;
	}


	public function setSort(array $sort)
	{
		$this->sort = $sort[$this->column];

		return $this;
	}


	public function getSortOpposite()
	{
		if ($this->sort == 'ASC') {
			return [$this->column => 'DESC'];
		}

		return [$this->column => 'ASC'];
	}


	public function isSortAsc()
	{
		return $this->sort == 'ASC';
	}


	public function setAlign($align)
	{
		$this->align = (string) $align;
	}


	public function hasAlign()
	{
		return (bool) $this->align;
	}


	public function getAlign()
	{
		return $this->align;
	}


	public function __call($name, $args)
	{
		$set_method = 'set' . ucfirst($name);

		if (method_exists($this, $set_method)) {
			return Nette\Utils\Callback::invokeArgs([$this, $set_method], $args);
		}
	}

}
