<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

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


	/**
	 * @param DataGrid $grid
	 */
	public function __construct(DataGrid $grid, $name)
	{
		$this->grid = $grid;
		$this->name = $name;

		$this->setTemplate(__DIR__ . '/../templates/column_multi_action.latte');
	}


	/**
	 * @return Html
	 */
	public function renderButton()
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
	 * @param string     $key
	 * @param string     $name
	 * @param string     $href
	 * @param array|null $params
	 * @return static
	 */
	public function addAction($key, $name, $href = null, array $params = null)
	{
		if (isset($this->actions[$key])) {
			throw new DataGridException(
				"There is already action at key [$key] defined for MultiAction."
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


	/**
	 * @param  string $key
	 * @return Action
	 */
	public function getAction($key)
	{
		if (!isset($this->actions[$key])) {
			throw new DataGridException(
				"There is no action at key [$key] defined for MultiAction."
			);
		}

		return $this->actions[$key];
	}


	/**
	 * Column can have variables that will be passed to custom template scope
	 * @return array
	 */
	public function getTemplateVariables()
	{
		return array_merge($this->template_variables, [
			'multi_action' => $this,
		]);
	}


	/**
	 * @param string $actionKey
	 * @param callable $rowCondition
	 * @return void
	 */
	public function setRowCondition($actionKey, callable $rowCondition)
	{
		$this->rowConditions[$actionKey] = $rowCondition;
	}


	/**
	 * @param  string $actionKey
	 * @param  Row    $row
	 * @return bool
	 */
	public function testRowCondition($actionKey, Row $row)
	{
		if (!isset($this->rowConditions[$actionKey])) {
			return true;
		}

		return (bool) call_user_func($this->rowConditions[$actionKey], $row->getItem());
	}
}
