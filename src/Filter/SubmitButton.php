<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\Filter;

use Nette\Forms\Controls\Button;
use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Traits\TButtonClass;
use Ublaboo\DataGrid\Traits\TButtonIcon;
use Ublaboo\DataGrid\Traits\TButtonText;
use Ublaboo\DataGrid\Traits\TButtonTitle;
use Ublaboo\DataGrid\Traits\TButtonTryAddIcon;

class SubmitButton extends Button
{

	use TButtonTryAddIcon;
	use TButtonIcon;
	use TButtonClass;
	use TButtonTitle;
	use TButtonText;

	/**
	 * @var DataGrid
	 */
	protected $grid;


	public function __construct(DataGrid $grid)
	{
		parent::__construct($this->text);

		$this->grid = $grid;

		$this->text = 'ublaboo_datagrid.filter_submit_button';
		$this->class = 'btn btn-sm btn-primary';
		$this->icon = 'search';

		$this->control = Html::el('button', ['type' => 'submit', 'name' => 'submit']);
	}


	/**
	 * @param string|object $caption
	 */
	public function getControl($caption = null): Html
	{
		$el = parent::getControl($caption);

		$el->setAttribute('type', 'submit');
		$el->setAttribute('class', $this->getClass());

		if ($this->getIcon() !== null) {
			$el->addHtml(
				Html::el('span')->appendAttribute(
					'class',
					DataGrid::$iconPrefix . $this->getIcon()
				)
			);

			if ($this->getText() !== '') {
				$el->addHtml('&nbsp;');
			}
		}

		$el->addText($this->grid->getTranslator()->translate($this->getText()));

		return $el;
	}
}
