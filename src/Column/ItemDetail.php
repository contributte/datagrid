<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\Column;

use LogicException;
use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridItemDetailException;
use Ublaboo\DataGrid\Row;
use Ublaboo\DataGrid\Traits\TButtonClass;
use Ublaboo\DataGrid\Traits\TButtonIcon;
use Ublaboo\DataGrid\Traits\TButtonText;
use Ublaboo\DataGrid\Traits\TButtonTitle;
use Ublaboo\DataGrid\Traits\TButtonTryAddIcon;
use Ublaboo\DataGrid\Traits\TRenderCondition;
use Ublaboo\DataGrid\Utils\ItemDetailForm;

class ItemDetail
{

	use TButtonTryAddIcon;
	use TButtonIcon;
	use TButtonClass;
	use TButtonTitle;
	use TButtonText;
	use TRenderCondition;

	/**
	 * (renderer | template | block)
	 *
	 * @var string|null
	 */
	protected $type;

	/**
	 * @var string|null
	 */
	protected $template;

	/**
	 * @var callable|null
	 */
	protected $renderer;

	/**
	 * @var DataGrid
	 */
	protected $grid;

	/**
	 * @var string
	 */
	protected $primaryWhereColumn;

	/**
	 * @var ItemDetailForm|null
	 */
	protected $form;

	/**
	 * @var array
	 */
	protected $templateParameters = [];


	public function __construct(DataGrid $grid, string $primaryWhereColumn)
	{
		$this->grid = $grid;
		$this->primaryWhereColumn = $primaryWhereColumn;

		$this->title = 'ublaboo_datagrid.show';
		$this->class = sprintf('btn btn-xs %s ajax', $grid::$btnSecondaryClass);
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
			->data('toggle-detail-grid-fullname', $this->grid->getFullName())
			->data('toggle-detail-grid', $this->grid->getName());

		$this->tryAddIcon($a, $this->getIcon(), $this->getText());

		$a->addText($this->text);

		if ($this->title !== null) {
			$a->setAttribute(
				'title',
				$this->grid->getTranslator()->translate($this->title)
			);
		}

		if ($this->class !== null) {
			$a->setAttribute('class', $this->class);
		}

		return $a;
	}


	/**
	 * @param mixed $item
	 * @return mixed
	 */
	public function render($item)
	{
		if ($this->getType() === 'block') {
			throw new DataGridItemDetailException('ItemDetail is set to render as block, but block #detail is not defined');
		}

		if ($this->getRenderer() === null) {
			throw new LogicException('Renderer is not set');
		}

		return call_user_func($this->getRenderer(), $item);
	}


	public function getPrimaryWhereColumn(): string
	{
		return $this->primaryWhereColumn;
	}


	/**
	 * Set item detail type
	 *
	 * @return static
	 */
	public function setType(string $type): self
	{
		$this->type = $type;

		return $this;
	}


	/**
	 * Get item detail type
	 */
	public function getType(): ?string
	{
		return $this->type;
	}


	/**
	 * Set item detail template
	 *
	 * @return static
	 */
	public function setTemplate(string $template): self
	{
		$this->template = $template;

		return $this;
	}


	/**
	 * Get item detail template
	 */
	public function getTemplate(): ?string
	{
		return $this->template;
	}


	/**
	 * @return static
	 */
	public function setRenderer(callable $renderer): self
	{
		$this->renderer = $renderer;

		return $this;
	}


	public function getRenderer(): ?callable
	{
		return $this->renderer;
	}


	/**
	 * @return static
	 */
	public function setForm(ItemDetailForm $form): self
	{
		$this->form = $form;

		return $this;
	}


	public function getForm(): ?ItemDetailForm
	{
		return $this->form;
	}


	/**
	 * @return static
	 */
	public function setTemplateParameters(array $templateParameters): self
	{
		$this->templateParameters = $templateParameters;

		return $this;
	}


	public function getTemplateVariables(): array
	{
		return $this->templateParameters;
	}

}
