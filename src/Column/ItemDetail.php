<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Column;

use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridItemDetailException;
use Ublaboo\DataGrid\Row;
use Ublaboo\DataGrid\Traits;
use Ublaboo\DataGrid\Utils\ItemDetailForm;

class ItemDetail
{

	use Traits\TButtonTryAddIcon;
	use Traits\TButtonIcon;
	use Traits\TButtonClass;
	use Traits\TButtonTitle;
	use Traits\TButtonText;
	use Traits\TRenderCondition;

	/**
	 * (renderer | template | block)
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * @var string
	 */
	protected $template;

	/**
	 * @var callable
	 */
	protected $renderer;

	/**
	 * @var DataGrid
	 */
	protected $grid;

	/**
	 * @var string|bool
	 */
	protected $primary_where_column;

	/**
	 * @var ItemDetailForm
	 */
	protected $form;

	/**
	 * @var array
	 */
	protected $template_parameters = [];

	public function __construct(DataGrid $grid, string $primary_where_column)
	{
		$this->grid = $grid;
		$this->primary_where_column = $primary_where_column;

		$this->title = 'ublaboo_datagrid.show';
		$this->class = 'btn btn-xs btn-default ajax';
		$this->icon = 'eye';
	}


	/**
	 * Render row item detail button
	 */
	public function renderButton(Row $row): Html
	{
		$a = Html::el('a')
			->href($this->grid->link('getItemDetail!', ['id' => $row->getId()]))
			->data('toggle-detail', $row->getId())
			->data('toggle-detail-grid', $this->grid->getName());

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
	 * Render item detail
	 *
	 * @param  mixed $item
	 * @return mixed
	 */
	public function render($item)
	{
		if ($this->getType() === 'block') {
			throw new DataGridItemDetailException(
				'ItemDetail is set to render as block, but block #detail is not defined'
			);
		}

		return call_user_func($this->getRenderer(), $item);
	}


	/**
	 * Get primary column for where clause
	 *
	 * @return string|bool
	 */
	public function getPrimaryWhereColumn()
	{
		return $this->primary_where_column;
	}


	/**
	 * Set item detail type
	 */
	public function setType(string $type)
	{
		$this->type = (string) $type;

		return $this;
	}


	/**
	 * Get item detail type
	 */
	public function getType(): string
	{
		return $this->type;
	}


	/**
	 * Set item detail template
	 */
	public function setTemplate(string $template)
	{
		$this->template = (string) $template;

		return $this;
	}


	/**
	 * Get item detail template
	 */
	public function getTemplate(): string
	{
		return $this->template;
	}


	/**
	 * Set item detail renderer
	 */
	public function setRenderer(callable $renderer)
	{
		$this->renderer = $renderer;

		return $this;
	}


	/**
	 * Get item detail renderer
	 */
	public function getRenderer(): callable
	{
		return $this->renderer;
	}


	/**
	 * @return static
	 */
	public function setForm(ItemDetailForm $form)
	{
		$this->form = $form;

		return $this;
	}


	public function getForm(): ItemDetailForm
	{
		return $this->form;
	}


	/**
	 * @param array $template_parameters
	 * @return static
	 */
	public function setTemplateParameters(array $template_parameters)
	{
		$this->template_parameters = $template_parameters;

		return $this;
	}


	public function getTemplateVariables()
	{
		return $this->template_parameters;
	}

}
