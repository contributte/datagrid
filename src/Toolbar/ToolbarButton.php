<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Toolbar;

use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Exception\DatagridColumnRendererException;
use Contributte\Datagrid\Traits\TButtonClass;
use Contributte\Datagrid\Traits\TButtonIcon;
use Contributte\Datagrid\Traits\TButtonRenderer;
use Contributte\Datagrid\Traits\TButtonText;
use Contributte\Datagrid\Traits\TButtonTitle;
use Contributte\Datagrid\Traits\TButtonTryAddIcon;
use Contributte\Datagrid\Traits\TLink;
use Nette\Utils\Html;

class ToolbarButton
{

	use TButtonTryAddIcon;
	use TButtonClass;
	use TButtonIcon;
	use TButtonRenderer;
	use TButtonText;
	use TButtonTitle;
	use TLink;

	protected array $attributes = [];

	protected ?string $confirmDialog = null;

	/**
	 * Toolbar button constructor
	 */
	public function __construct(protected Datagrid $grid, protected string $href, string $text, protected array $params = [])
	{
		$this->text = $text;
	}

	/**
	 * Render toolbar button
	 */
	public function renderButton(): Html
	{
		try {
			// Renderer function may be used
			return $this->useRenderer();
		} catch (DatagridColumnRendererException) {
			// Do not use renderer
		}

		$link = $this->createLink($this->grid, $this->href, $this->params);

		$a = Html::el('a')->href($link);

		$this->tryAddIcon($a, $this->getIcon(), $this->getText());

		if ($this->attributes !== []) {
			$a->addAttributes($this->attributes);
		}

		$a->addText($this->grid->getTranslator()->translate($this->text));

		if ($this->getTitle() !== null) {
			$a->setAttribute(
				'title',
				$this->grid->getTranslator()->translate($this->getTitle())
			);
		}

		$a->setAttribute('class', $this->getClass());

		if ($this->confirmDialog !== null) {
			$a->setAttribute('data-datagrid-confirm', $this->confirmDialog);
		}

		return $a;
	}

	/**
	 * @return static
	 */
	public function addAttributes(array $attrs): static
	{
		$this->attributes += $attrs;

		return $this;
	}

	/**
	 * Add Confirm dialog
	 */
	public function setConfirmDialog(string $confirmDialog): self
	{
		$this->confirmDialog = $confirmDialog;

		return $this;
	}

}
