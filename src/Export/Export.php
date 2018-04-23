<?php declare(strict_types = 1);

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Export;

use Nette\Utils\Callback;
use Nette\Utils\Html;
use Ublaboo;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Traits;

class Export
{

	use Traits\TButtonTryAddIcon;
	use Traits\TButtonIcon;
	use Traits\TButtonClass;
	use Traits\TButtonTitle;
	use Traits\TButtonText;

	/**
	 * @var callable
	 */
	protected $callback;

	/**
	 * @var bool
	 */
	protected $ajax;

	/**
	 * @var bool
	 */
	protected $filtered;

	/**
	 * @var string
	 */
	protected $link;

	/**
	 * @var array
	 */
	protected $columns = [];

	/**
	 * @var DataGrid
	 */
	protected $grid;
	
	/**
	 * @var null|string
	 */
	protected $confirmDialog = null;
	
	/**
	 * @param DataGrid   $grid
	 * @param string     $text
	 * @param callable   $callback
	 * @param bool    $filtered
	 */
	public function __construct(DataGrid $grid, string $text, callable $callback, bool $filtered)
	{
		$this->grid = $grid;
		$this->text = $text;
		$this->callback = $callback;
		$this->filtered = (bool) $filtered;
		$this->title = $text;
	}


	/**
	 * Render export button
	 *
	 * @return Html
	 */
	public function render(): Html
	{
		$a = Html::el('a', [
			'class' => [$this->class],
			'title' => $this->grid->getTranslator()->translate($this->getTitle()),
			'href' => $this->link,
		]);

		$this->tryAddIcon(
			$a,
			$this->getIcon(),
			$this->grid->getTranslator()->translate($this->getTitle())
		);

		$a->addText($this->grid->getTranslator()->translate($this->text));

		if ($this->isAjax()) {
			$a->class[] = 'ajax';
		}
		
		if ($this->confirmDialog !== null) {
			$a->setAttribute('data-datagrid-confirm', $this->confirmDialog);
		}
		
		
		return $a;
	}
	
	/**
	 * Add Confirm dialog
	 * @param string $confirmDialog
	 * @return self
	 */
	public function setConfirmDialog($confirmDialog)
	{
		$this->confirmDialog = $confirmDialog;
		
		return $this;
	}
	
	
	/**
	 * Tell export which columns to use when exporting data
	 *
	 * @param array $columns
	 * @return self
	 */
	public function setColumns(array $columns): self
	{
		$this->columns = $columns;

		return $this;
	}


	/**
	 * Get columns for export
	 *
	 * @return array
	 */
	public function getColumns(): array
	{
		return $this->columns;
	}


	/**
	 * Export signal url
	 *
	 * @param string $link
	 * @return self
	 */
	public function setLink(string $link): self
	{
		$this->link = $link;

		return $this;
	}


	/**
	 * Tell export whether to be called via ajax or not
	 *
	 * @param bool $ajax
	 */
	public function setAjax(bool $ajax = true)
	{
		$this->ajax = (bool) $ajax;

		return $this;
	}


	/**
	 * Is export called via ajax?
	 *
	 * @return bool
	 */
	public function isAjax(): bool
	{
		return $this->ajax;
	}


	/**
	 * Is export filtered?
	 *
	 * @return bool
	 */
	public function isFiltered(): bool
	{
		return $this->filtered;
	}


	/**
	 * Call export callback
	 *
	 * @param  array    $data
	 * @return void
	 */
	public function invoke(array $data): void
	{
		Callback::invokeArgs($this->callback, [$data, $this->grid]);
	}

}
