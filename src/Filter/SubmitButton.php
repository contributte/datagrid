<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Filter;

use Nette;
use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Traits;

class SubmitButton extends Nette\Forms\Controls\Button
{
	use Traits\TButtonTryAddIcon;
	use Traits\TButtonIcon;
	use Traits\TButtonClass;
	use Traits\TButtonTitle;
	use Traits\TButtonText;

	/**
	 * @var DataGrid
	 */
	protected $grid;


	/**
	 * @param DataGrid     $grid
	 */
	public function __construct($grid)
	{
		parent::__construct($this->text);

		$this->grid = $grid;

		$this->text = 'ublaboo_datagrid.filter_submit_button';
		$this->class = 'btn btn-sm btn-primary';
		$this->icon = 'search';

		$this->control = Html::el('button', ['type' => 'submit', 'name' => 'submit']);
	}


	/**
	 * Generates control's HTML element.
	 * @param  string
	 * @return Nette\Utils\Html
	 */
	public function getControl($caption = null)
	{
		$el = parent::getControl('');

		$el->type = 'submit';
		$el->class = $this->getClass();

		if ($this->getIcon()) {
			$el->addHtml(Html::el('span')->class(DataGrid::$icon_prefix . $this->getIcon()));

			if (strlen($this->getText())) {
				$el->addHtml('&nbsp;');
			}
		}

		$el->addText($this->grid->getTranslator()->translate($this->getText()));

		return $el;
	}
}
