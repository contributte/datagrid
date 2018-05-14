<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Column;

use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridException;
use Ublaboo\DataGrid\Row;
use Ublaboo\DataGrid\Traits;

class MultiAction extends Column
{

	use Traits\TButtonTryAddIcon;
	use Traits\TButtonIcon;
	use Traits\TButtonClass;
	use Traits\TButtonTitle;
	use Traits\TButtonText;
	use Traits\TButtonCaret;
	use Traits\TLink;

	/**
	 * @var DataGrid
	 */
	protected $grid;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var array
	 */
	protected $actions = [];

	/**
	 * @var callable[]
	 */
	private $rowConditions = [];

	public function __construct(DataGrid $grid, $name)
	{
		$this->grid = $grid;
		$this->name = $name;

		$this->setTemplate(__DIR__ . '/../templates/column_multi_action.latte');
	}


	public function renderButton(): Html
	{
		$button = Html::el('button')
			->type('button')
			->data('toggle', 'dropdown');

		$this->tryAddIcon($button, $this->getIcon(), $this->getName());

		if (!empty($this->attributes)) {
			$button->addAttributes($this->attributes);
		}

		$button->addText($this->grid->getTranslator()->translate($this->name));

		if ($this->hasCaret()) {
			$button->addHtml('&nbsp;');
			$button->addHtml('<i class="caret"></i>');
		}

		if ($this->getTitle()) {
			$button->title($this->grid->getTranslator()->translate($this->getTitle()));
		}

		if ($this->getClass()) {
			$button->class($this->getClass() . ' dropdown-toggle');
		}

		return $button;
	}


	/**
	 * @param array|null $params
	 * @return static
	 */
	public function addAction(string $key, string $name, ?string $href = null, ?array $params = null)
	{
		if (isset($this->actions[$key])) {
			throw new DataGridException(
				sprintf('There is already action at key [%s] defined for MultiAction.', $key)
			);
		}

		$href = $href ?: $key;

		if ($params === null) {
			$params = [$this->grid->getPrimaryKey()];
		}

		$action = new Action($this->grid, $href, $name, $params);

		$action->setClass('');

		$this->actions[$key] = $action;

		return $this;
	}


	/**
	 * @return Action[]
	 */
	public function getActions()
	{
		return $this->actions;
	}


	public function getAction(string $key): Action
	{
		if (!isset($this->actions[$key])) {
			throw new DataGridException(
				sprintf('There is no action at key [%s] defined for MultiAction.', $key)
			);
		}

		return $this->actions[$key];
	}


	/**
	 * Column can have variables that will be passed to custom template scope
	 *
	 * @return array
	 */
	public function getTemplateVariables(): array
	{
		return array_merge($this->template_variables, [
			'multi_action' => $this,
		]);
	}


	public function setRowCondition(string $actionKey, callable $rowCondition): void
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
