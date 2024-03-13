<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Column;

use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Exception\DatagridColumnRendererException;
use Contributte\Datagrid\Row;
use Nette\Utils\Html;

class ColumnLink extends Column
{

	protected ?string $title = null;

	protected ?string $class = null;

	protected ?string $icon = null;

	protected array $dataAttributes = [];

	protected bool $openInNewTab = false;

	protected array $parameters = [];

	public function __construct(
		Datagrid $grid,
		string $key,
		string $column,
		string $name,
		protected string $href,
		protected array $params
	)
	{
		parent::__construct($grid, $key, $column, $name);
	}

	public function render(Row $row): mixed
	{
		/**
		 * Renderer function may be used
		 */
		try {
			return $this->useRenderer($row);
		} catch (DatagridColumnRendererException) {
			/**
			 * Do not use renderer
			 */
		}

		$value = parent::render($row);

		if (! (bool) $value && $this->icon !== null) {
			return null;
		}

		$a = Html::el('a')
			->href($this->createLink(
				$this->grid,
				$this->href,
				$this->getItemParams($row, $this->params) + $this->parameters
			));

		if ($this->dataAttributes !== []) {
			foreach ($this->dataAttributes as $key => $attrValue) {
				$a->data((string) $key, $attrValue);
			}
		}

		if ($this->openInNewTab) {
			$a->addAttributes(['target' => '_blank']);
		}

		if ($this->title !== null) {
			$a->setAttribute('title', $this->title);
		}

		if ($this->class !== null) {
			$a->setAttribute('class', $this->class);
		}

		$element = $a;

		if ($this->icon !== null) {
			$a->addHtml(
				Html::el('span')->setAttribute('class', Datagrid::$iconPrefix . $this->icon)
			);

			if (strlen($value) > 0) {
				$a->addHtml('&nbsp;');
			}
		}

		if ($this->isTemplateEscaped()) {
			$a->addText($value);
		} else {
			$a->addHtml($value);
		}

		return $element;
	}

	/**
	 * @return static
	 */
	public function addParameters(array $parameters): self
	{
		$this->parameters = $parameters;

		return $this;
	}

	/**
	 * @return static
	 */
	public function setIcon(?string $icon = null): self
	{
		$this->icon = $icon;

		return $this;
	}

	/**
	 * @return static
	 */
	public function setDataAttribute(string $key, mixed $value): self
	{
		$this->dataAttributes[$key] = $value;

		return $this;
	}

	/**
	 * @return static
	 */
	public function setTitle(string $title): self
	{
		$this->title = $title;

		return $this;
	}

	public function getTitle(): ?string
	{
		return $this->title;
	}

	/**
	 * @return static
	 */
	public function setClass(?string $class): self
	{
		$this->class = $class;

		return $this;
	}

	public function getClass(): ?string
	{
		return $this->class;
	}

	public function isOpenInNewTab(): bool
	{
		return $this->openInNewTab;
	}

	/**
	 * @return static
	 */
	public function setOpenInNewTab(bool $openInNewTab = true): self
	{
		$this->openInNewTab = $openInNewTab;

		return $this;
	}

}
