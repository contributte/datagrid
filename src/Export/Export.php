<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Export;

use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Traits\TButtonClass;
use Contributte\Datagrid\Traits\TButtonIcon;
use Contributte\Datagrid\Traits\TButtonText;
use Contributte\Datagrid\Traits\TButtonTitle;
use Contributte\Datagrid\Traits\TButtonTryAddIcon;
use Nette\Application\UI\Link;
use Nette\Utils\Html;

class Export
{

	use TButtonTryAddIcon;
	use TButtonIcon;
	use TButtonClass;
	use TButtonTitle;
	use TButtonText;

	/** @var callable */
	protected $callback;

	protected bool $ajax = false;

	protected ?Link $link = null;

	protected array $columns = [];

	protected ?string $confirmDialog = null;

	protected ?string $target = null;

	public function __construct(
		protected Datagrid $grid,
		string $text,
		callable $callback,
		protected bool $filtered
	)
	{
		$this->text = $text;
		$this->callback = $callback;
		$this->title = $text;
	}

	public function render(): Html
	{
		$a = Html::el('a', [
			'class' => [$this->class],
			'title' => $this->grid->getTranslator()->translate($this->getTitle()),
			'href' => $this->link,
			'target' => $this->target,
		]);

		$this->tryAddIcon(
			$a,
			$this->getIcon(),
			$this->grid->getTranslator()->translate($this->getTitle())
		);

		$a->addText($this->grid->getTranslator()->translate($this->text));

		if ($this->isAjax()) {
			$a->appendAttribute('class', 'ajax');
		}

		if ($this->confirmDialog !== null) {
			$a->setAttribute('data-datagrid-confirm', $this->confirmDialog);
		}

		return $a;
	}

	/**
	 * @return static
	 */
	public function setConfirmDialog(string $confirmDialog): self
	{
		$this->confirmDialog = $confirmDialog;

		return $this;
	}

	/**
	 * Tell export which columns to use when exporting data
	 *
	 * @return static
	 */
	public function setColumns(array $columns): self
	{
		$this->columns = $columns;

		return $this;
	}

	/**
	 * Get columns for export
	 */
	public function getColumns(): array
	{
		return $this->columns;
	}

	/**
	 * Export signal url
	 *
	 * @return static
	 */
	public function setLink(Link $link): self
	{
		$this->link = $link;

		return $this;
	}

	/**
	 * Tell export whether to be called via ajax or not
	 *
	 * @return static
	 */
	public function setAjax(bool $ajax = true): self
	{
		$this->ajax = $ajax;

		return $this;
	}

	public function isAjax(): bool
	{
		return $this->ajax;
	}

	/**
	 * Is export filtered?
	 */
	public function isFiltered(): bool
	{
		return $this->filtered;
	}

	/**
	 * Call export callback
	 */
	public function invoke(iterable $data): void
	{
		($this->callback)($data, $this->grid);
	}

	/**
	 * Adds target to html:a [_blank, _self, _parent, _top]
	 */
	public function setTarget(?string $target = null): void
	{
		if (in_array($target, ['_blank', '_self', '_parent', '_top'], true)) {
			$this->target = $target;
		}
	}

}
