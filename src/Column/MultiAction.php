<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Column;

use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Exception\DatagridException;
use Contributte\Datagrid\Row;
use Contributte\Datagrid\Traits\TButtonCaret;
use Contributte\Datagrid\Traits\TButtonClass;
use Contributte\Datagrid\Traits\TButtonIcon;
use Contributte\Datagrid\Traits\TButtonText;
use Contributte\Datagrid\Traits\TButtonTitle;
use Contributte\Datagrid\Traits\TButtonTryAddIcon;
use Contributte\Datagrid\Traits\TLink;
use Nette\Utils\Html;

class MultiAction extends Column
{

	use TButtonTryAddIcon;
	use TButtonIcon;
	use TButtonClass;
	use TButtonTitle;
	use TButtonText;
	use TButtonCaret;
	use TLink;

	protected Datagrid $grid;

	protected string $name;

	protected array $actions = [];

	/** @var array|callable[] */
	private array $rowConditions = [];

	public function __construct(Datagrid $grid, string $key, string $name)
	{
		parent::__construct($grid, $key, '', $name);

		$this->setTemplate(__DIR__ . '/../templates/column_multi_action.latte');
	}

	public function renderButton(): Html
	{
		$button = Html::el('button')
			->setAttribute('type', 'button')
			->data('bs-toggle', 'dropdown');

		$this->tryAddIcon($button, $this->getIcon(), $this->getName());

		$button->addText($this->grid->getTranslator()->translate($this->name));

		if ($this->hasCaret()) {
			$button->addHtml('&nbsp;');
			$button->addHtml('<i class="caret"></i>');
		}

		if ($this->getTitle() !== null) {
			$button->setAttribute(
				'title',
				$this->grid->getTranslator()->translate($this->getTitle())
			);
		}

		if ($this->getClass() !== '') {
			$button->setAttribute('class', $this->getClass() . ' dropdown-toggle');
		}

		return $button;
	}

	/**
	 * @return static
	 */
	public function addAction(
		string $key,
		string $name,
		?string $href = null,
		?array $params = null
	): self
	{
		if (isset($this->actions[$key])) {
			throw new DatagridException(
				sprintf('There is already action at key [%s] defined for MultiAction.', $key)
			);
		}

		$href ??= $key;

		if ($params === null) {
			$params = [$this->grid->getPrimaryKey()];
		}

		$action = new Action($this->grid, $key, $href, $name, $params);

		$action->setClass('dropdown-item datagrid-multiaction-dropdown-item');

		$this->actions[$key] = $action;

		return $this;
	}

	/**
	 * @return array<Action>
	 */
	public function getActions(): array
	{
		return $this->actions;
	}

	public function getAction(string $key): Action
	{
		if (!isset($this->actions[$key])) {
			throw new DatagridException(
				sprintf('There is no action at key [%s] defined for MultiAction.', $key)
			);
		}

		return $this->actions[$key];
	}

	/**
	 * Column can have variables that will be passed to custom template scope
	 */
	public function getTemplateVariables(): array
	{
		return array_merge($this->templateVariables, [
			'multiAction' => $this,
		]);
	}

	public function setRowCondition(
		string $actionKey,
		callable $rowCondition
	): void
	{
		$this->rowConditions[$actionKey] = $rowCondition;
	}

	public function testRowCondition(string $actionKey, Row $row): bool
	{
		if (!isset($this->rowConditions[$actionKey])) {
			return true;
		}

		return (bool) call_user_func($this->rowConditions[$actionKey], $row->getItem());
	}

}
