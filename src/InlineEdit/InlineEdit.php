<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\InlineEdit;

use Nette;
use Nette\Forms\Container;
use Nette\SmartObject;
use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Row;
use Ublaboo\DataGrid\Traits\TButtonClass;
use Ublaboo\DataGrid\Traits\TButtonIcon;
use Ublaboo\DataGrid\Traits\TButtonText;
use Ublaboo\DataGrid\Traits\TButtonTitle;
use Ublaboo\DataGrid\Traits\TButtonTryAddIcon;

/**
 * @method onSubmit($id, Nette\Utils\ArrayHash $values)
 * @method onControlAdd(Nette\Forms\Container $container)
 * @method onControlAfterAdd(Nette\Forms\Container $container)
 * @method onSetDefaults(Nette\Forms\Container $container, $item)
 * @method onCustomRedraw(string $buttonName)
 */
class InlineEdit
{

	use SmartObject;
	use TButtonTryAddIcon;
	use TButtonIcon;
	use TButtonClass;
	use TButtonTitle;
	use TButtonText;

	/**
	 * @var array|callable[]
	 */
	public $onSubmit = [];

	/**
	 * @var array|callable[]
	 */
	public $onControlAdd = [];

	/**
	 * @var array|callable[]
	 */
	public $onControlAfterAdd = [];

	/**
	 * @var array|callable[]
	 */
	public $onSetDefaults = [];

	/**
	 * @var array|callable[]
	 */
	public $onCustomRedraw = [];

	/**
	 * @var mixed
	 */
	protected $itemID;

	/**
	 * @var DataGrid
	 */
	protected $grid;

	/**
	 * @var string|null
	 */
	protected $primaryWhereColumn = null;

	/**
	 * Inline adding - render on the top or in the bottom?
	 *
	 * @var bool
	 */
	protected $positionTop = false;

	/**
	 * Columns that are not edited can displey normal value instaad of nothing..
	 *
	 * @var bool
	 */
	protected $showNonEditingColumns = true;


	public function __construct(DataGrid $grid, ?string $primaryWhereColumn = null)
	{
		$this->grid = $grid;
		$this->primaryWhereColumn = $primaryWhereColumn;

		$this->title = 'ublaboo_datagrid.edit';
		$this->class = sprintf('btn btn-xs %s ajax', $grid::$btnSecondaryClass);
		$this->icon = 'pencil pencil-alt';

		$this->onControlAfterAdd[] = [$this, 'addControlsClasses'];
	}


	/**
	 * @param mixed $id
	 * @return static
	 */
	public function setItemId($id): self
	{
		$this->itemID = $id;

		return $this;
	}


	/**
	 * @return mixed
	 */
	public function getItemId()
	{
		return $this->itemID;
	}


	public function getPrimaryWhereColumn(): ?string
	{
		return $this->primaryWhereColumn;
	}


	public function renderButton(Row $row): Html
	{
		$a = Html::el('a')
			->href($this->grid->link('inlineEdit!', ['id' => $row->getId()]));

		$this->tryAddIcon($a, $this->getIcon(), $this->getText());

		$a->addText($this->text);

		if ($this->title !== null) {
			$a->setAttribute(
				'title',
				$this->grid->getTranslator()->translate($this->title)
			);
		}

		if ($this->class !== null) {
			$a->appendAttribute('class', $this->class);
		}

		$a->appendAttribute('class', 'datagrid-inline-edit-trigger');

		return $a;
	}


	/**
	 * Render row item detail button
	 */
	public function renderButtonAdd(): Html
	{
		$a = Html::el('a')
			->href($this->grid->link('showInlineAdd!'));

		$this->tryAddIcon($a, $this->getIcon(), $this->getText());

		$a->addText($this->text);

		if ($this->title !== null) {
			$a->setAttribute(
				'title',
				$this->grid->getTranslator()->translate($this->title)
			);
		}

		if ($this->class !== null) {
			$a->appendAttribute('class', $this->class);
		}

		return $a;
	}


	/**
	 * @return static
	 */
	public function setPositionTop(bool $positionTop = true): self
	{
		$this->positionTop = $positionTop;

		return $this;
	}


	public function isPositionTop(): bool
	{
		return $this->positionTop;
	}


	public function isPositionBottom(): bool
	{
		return !$this->positionTop;
	}


	public function addControlsClasses(Container $container): void
	{
		foreach ($container->getControls() as $key => $control) {
			switch ($key) {
				case 'submit':
					$control->setValidationScope([$container]);
					$control->setAttribute('class', 'btn btn-xs btn-primary');

					break;
				case 'cancel':
					$control->setValidationScope([]);
					$control->setAttribute('class', 'btn btn-xs btn-danger');

					break;
				default:
					if ($control->getControl()->getAttribute('class') === null) {
						$control->setAttribute('class', 'form-control input-sm form-control-sm');
					}

					break;
			}
		}
	}


	/**
	 * @return static
	 */
	public function setShowNonEditingColumns(bool $show = true): self
	{
		$this->showNonEditingColumns = $show;

		return $this;
	}


	public function showNonEditingColumns(): bool
	{
		return $this->showNonEditingColumns;
	}
}
