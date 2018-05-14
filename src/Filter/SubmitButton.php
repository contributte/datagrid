<?php declare(strict_types = 1);

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
	 * Generates control's HTML element.
	 *
	 * @param  string
	 */
	public function getControl(?string $caption = null): Nette\Utils\Html
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
