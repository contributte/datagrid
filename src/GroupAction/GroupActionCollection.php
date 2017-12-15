<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\GroupAction;

use Nette;
use Nette\SmartObject;
use Nette\Application\UI\Form;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridGroupActionException;

class GroupActionCollection
{

	use SmartObject;

	const ID_ATTRIBUTE_PREFIX = 'group_action_item_';

	/**
	 * @var GroupAction[]
	 */
	protected $group_actions = [];

	/**
	 * @var DataGrid
	 */
	protected $datagrid;


	public function __construct(DataGrid $datagrid)
	{
		$this->datagrid = $datagrid;
	}


	/**
	 * Get assambled form
	 * @param Nette\Forms\Container $container
	 * @return void
	 */
	public function addToFormContainer($container)
	{
		/** @var Nette\Application\UI\Form $form */
		$form = $container->lookup('Nette\Application\UI\Form');
		$translator = $form->getTranslator();
		$main_options = [];

		/**
		 * First foreach for filling "main" select
		 */
		foreach ($this->group_actions as $id => $action) {
			$main_options[$id] = $action->getTitle();
		}

		$container->addSelect('group_action', '', $main_options)
			->setPrompt('ublaboo_datagrid.choose');

		/**
		 * Second for creating select for each "sub"-action
		 */
		foreach ($this->group_actions as $id => $action) {
			$control = null;

			if ($action instanceof GroupSelectAction) {
				if ($action->hasOptions()) {
					if ($action instanceof GroupMultiSelectAction) {
						$control = $container->addMultiSelect($id, '', $action->getOptions());
						$control->setAttribute('data-datagrid-multiselect-id', static::ID_ATTRIBUTE_PREFIX . $id);
						$control->setAttribute('data-style', 'hidden');
						$control->setAttribute('data-selected-icon-check', DataGrid::$icon_prefix . 'check');
					} else {
						$control = $container->addSelect($id, '', $action->getOptions());
					}

					$control->setAttribute('id', static::ID_ATTRIBUTE_PREFIX . $id);
				}

			} elseif ($action instanceof GroupTextAction) {
				$control = $container->addText($id, '');

				$control->setAttribute('id', static::ID_ATTRIBUTE_PREFIX . $id)
					->addConditionOn($container['group_action'], Form::EQUAL, $id)
						->setRequired($translator->translate('ublaboo_datagrid.choose_input_required'))
					->endCondition();

			} elseif ($action instanceof GroupTextareaAction) {
				$control = $container->addTextarea($id, '');

				$control->setAttribute('id', static::ID_ATTRIBUTE_PREFIX . $id)
					->addConditionOn($container['group_action'], Form::EQUAL, $id)
						->setRequired($translator->translate('ublaboo_datagrid.choose_input_required'));
			}

			if ($control) {
				/**
				 * User may set a class to the form control
				 */
				if ($class = $action->getClass()) {
					$control->setAttribute('class', $class);
				}

				/**
				 * User may set additional attribtues to the form control
				 */
				foreach ($action->getAttributes() as $name => $value) {
					$control->setAttribute($name, $value);
				}
			}
		}

		foreach ($this->group_actions as $id => $action) {
			$container['group_action']->addCondition(Form::EQUAL, $id)
				->toggle(static::ID_ATTRIBUTE_PREFIX . $id);
		}

		$container['group_action']->addCondition(Form::FILLED)
			->toggle(strtolower($this->datagrid->getName()) . 'group_action_submit');

		$container->addSubmit('submit', 'ublaboo_datagrid.execute')
			->setValidationScope([$container])
			->setAttribute('id', strtolower($this->datagrid->getName()) . 'group_action_submit');

		if ($form instanceof Nette\ComponentModel\IComponent) {
			$form->onSubmit[] = [$this, 'submitted'];
		}
	}


	/**
	 * Pass "sub"-form submission forward to custom submit function
	 * @param  Form   $form
	 * @return void
	 */
	public function submitted(Form $form)
	{
		if (!isset($form['group_action']['submit']) || !$form['group_action']['submit']->isSubmittedBy()) {
			return;
		}

		$values = $form->getValues();
		$values = $values['group_action'];

		if ($values->group_action === 0 || $values->group_action === null) {
			return;
		}

		/**
		 * @todo Define items IDs
		 */
		$http_ids = $form->getHttpData(Form::DATA_LINE | Form::DATA_KEYS, strtolower($this->datagrid->getName()) . '_group_action_item[]');
		$ids = array_keys($http_ids);

		$id = $values->group_action;
		$this->group_actions[$id]->onSelect($ids, isset($values->{$id}) ? $values->{$id} : null);

		$form['group_action']['group_action']->setValue(null);
	}


	/**
	 * Add one group action (select box) to collection of actions
	 *
	 * @param string $title
	 * @param array  $options
	 *
	 * @return GroupAction
	 */
	public function addGroupSelectAction($title, $options)
	{
		$id = ($s = sizeof($this->group_actions)) ? ($s + 1) : 1;

		return $this->group_actions[$id] = new GroupSelectAction($title, $options);
	}


	/**
	 * Add one group action (multiselect box) to collection of actions
	 *
	 * @param string $title
	 * @param array  $options
	 *
	 * @return GroupAction
	 */
	public function addGroupMultiSelectAction($title, $options)
	{
		$id = ($s = sizeof($this->group_actions)) ? ($s + 1) : 1;

		return $this->group_actions[$id] = new GroupMultiSelectAction($title, $options);
	}


	/**
	 * Add one group action (text input) to collection of actions
	 *
	 * @param string $title
	 *
	 * @return GroupAction
	 */
	public function addGroupTextAction($title)
	{
		$id = ($s = sizeof($this->group_actions)) ? ($s + 1) : 1;

		return $this->group_actions[$id] = new GroupTextAction($title);
	}


	/**
	 * Add one group action (textarea) to collection of actions
	 *
	 * @param string $title
	 *
	 * @return GroupAction
	 */
	public function addGroupTextareaAction($title)
	{
		$id = ($s = sizeof($this->group_actions)) ? ($s + 1) : 1;

		return $this->group_actions[$id] = new GroupTextareaAction($title);
	}


	/**
	 * @param  string $title
	 * @return GroupAction
	 */
	public function getGroupAction($title)
	{
		foreach ($this->group_actions as $action) {
			if ($action->getTitle() === $title) {
				return $action;
			}
		}

		throw new DataGridGroupActionException("Group action $title does not exist.");
	}
}
