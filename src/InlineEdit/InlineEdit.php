<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\InlineEdit;

use Nette;
use Nette\SmartObject;
use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Traits;

/**
 * @method onSubmit($id, Nette\Utils\ArrayHash $values)
 * @method onSubmit(Nette\Utils\ArrayHash $values)
 * @method onControlAdd(Nette\Forms\Container $container)
 * @method onControlAfterAdd(Nette\Forms\Container $container)
 * @method onSetDefaults(Nette\Forms\Container $container, $item)
 */
class InlineEdit
{

	use SmartObject;

	use Traits\TButtonTryAddIcon;
	use Traits\TButtonIcon;
	use Traits\TButtonClass;
	use Traits\TButtonTitle;
	use Traits\TButtonText;

	/**
	 * @var callable[]
	 */
	public $onSubmit;

	/**
	 * @var callable[]
	 */
	public $onControlAdd;

	/**
	 * @var callable[]
	 */
	public $onControlAfterAdd;

	/**
	 * @var callable[]
	 */
	public $onSetDefaults;

	/**
	 * @var callable[]
	 */
	public $onCustomRedraw;

	/**
	 * @var mixed
	 */
	protected $item_id;

	/**
	 * @var DataGrid
	 */
	protected $grid;

	/**
	 * @var string|NULL
	 */
	protected $primary_where_column;

	/**
	 * Inline adding - render on the top or in the bottom?
	 *
	 * @var bool
	 */
	protected $position_top = false;

	/**
	 * Columns that are not edited can displey normal value instaad of nothing..
	 *
	 * @var bool
	 */
	protected $showNonEditingColumns = true;

	public function __construct(DataGrid $grid, ?string $primary_where_column = null)
	{
		$this->grid = $grid;
		$this->primary_where_column = $primary_where_column;

		$this->title = 'ublaboo_datagrid.edit';
		$this->class = 'btn btn-xs btn-default ajax';
		$this->icon = 'pencil';

		$this->onControlAfterAdd[] = [$this, 'addControlsClasses'];
	}


	/**
	 * @param mixed $id
	 * @return static
	 */
	public function setItemId($id)
	{
		$this->item_id = $id;

		return $this;
	}


	/**
	 * @return mixed
	 */
	public function getItemId()
	{
		return $this->item_id;
	}


	public function getPrimaryWhereColumn(): string
	{
		return $this->primary_where_column;
	}


	/**
	 * Render row item detail button
	 */
	public function renderButton(Row $row): Html
	{
		$a = Html::el('a')
			->href($this->grid->link('inlineEdit!', ['id' => $row->getId()]));

		$this->tryAddIcon($a, $this->getIcon(), $this->getText());

		$a->addText($this->text);

		if ($this->title) {
			$a->title($this->grid->getTranslator()->translate($this->title));
		}
		if ($this->class) {
			$a->class[] = $this->class;
		}

		$a->class[] = 'datagrid-inline-edit-trigger';

		return $a;
	}


	/**
	 * Render row item detail button
	 */
	public function renderButtonAdd(): Html
	{
		$a = Html::el('a')->data('datagrid-toggle-inline-add', true);

		$this->tryAddIcon($a, $this->getIcon(), $this->getText());

		$a->addText($this->text);

		if ($this->title) {
			$a->title($this->grid->getTranslator()->translate($this->title));
		}
		if ($this->class) {
			$a->class($this->class);
		}

		return $a;
	}


	/**
	 * Setter for inline adding position
	 *
	 * @return static
	 */
	public function setPositionTop(bool $position_top = true)
	{
		$this->position_top = (bool) $position_top;

		return $this;
	}


	/**
	 * Getter for inline adding
	 */
	public function isPositionTop(): bool
	{
		return $this->position_top;
	}


	public function isPositionBottom(): bool
	{
		return !$this->position_top;
	}


	public function addControlsClasses(Nette\Forms\Container $container): void
	{
		foreach ($container->getControls() as $key => $control) {
			switch ($key) {
				case 'submit':
					$control->setValidationScope([$container]);
					$control->setAttribute('class', 'btn btn-xs btn-primary');

					break;

				case 'cancel':
					$control->setAttribute('class', 'btn btn-xs btn-danger');

					break;

				default:
					if (empty($control->getControl()->getClass())) {
						$control->setAttribute('class', 'form-control input-sm');
					}

					break;
			}
		}
	}


	/**
	 * @return static
	 */
	public function setShowNonEditingColumns(bool $show = true)
	{
		$this->showNonEditingColumns = (bool) $show;

		return $this;
	}


	public function showNonEditingColumns(): bool
	{
		return $this->showNonEditingColumns;
	}

}
