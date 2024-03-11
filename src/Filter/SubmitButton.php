<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Filter;

use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Traits\TButtonClass;
use Contributte\Datagrid\Traits\TButtonIcon;
use Contributte\Datagrid\Traits\TButtonText;
use Contributte\Datagrid\Traits\TButtonTitle;
use Contributte\Datagrid\Traits\TButtonTryAddIcon;
use Nette\Forms\Controls\Button;
use Nette\Utils\Html;
use Stringable;

class SubmitButton extends Button
{

	use TButtonTryAddIcon;
	use TButtonIcon;
	use TButtonClass;
	use TButtonTitle;
	use TButtonText;

	public function __construct(protected Datagrid $grid)
	{
		parent::__construct($this->text);

		$this->text = 'contributte_datagrid.filter_submit_button';
		$this->class = 'btn btn-sm btn-primary';
		$this->icon = 'search';

		$this->control = Html::el('button', ['type' => 'submit', 'name' => 'submit']);
	}

	public function getControl(Stringable|string|null $caption = null): Html
	{
		$el = parent::getControl($caption);

		$el->setAttribute('type', 'submit');
		$el->setAttribute('class', $this->getClass());

		if ($this->getIcon() !== null) {
			$el->addHtml(
				Html::el('span')->appendAttribute(
					'class',
					Datagrid::$iconPrefix . $this->getIcon()
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
