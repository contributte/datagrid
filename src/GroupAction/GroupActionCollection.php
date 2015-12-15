<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\GroupAction;

use Nette,
	Nette\Application\UI\Form;

class GroupActionCollection extends Nette\Object
{

	const ID_ATTRIBUTE_PREFIX = 'group_action_item_';


	/**
	 * @var GroupAction[]
	 */
	protected $group_actions = [];


	/**
	 * Get assambled form
	 * @return Nette\Application\UI\Form
	 */
	public function getFormComponent()
	{
		$form = new Form;

		/**
		 * First foreach for filling "main" select
		 */
		foreach ($this->group_actions as $id => $action) {
			$main_options[$id] = $action->getTitle();
		}

		$form->addSelect('group_action', '', $main_options)
			->setPrompt('Vyberte')
			->setRequired('Vyberte, prosím, akci');

		/**
		 * Second for creating select for each "sub"-action
		 */
		foreach ($this->group_actions as $id => $action) {
			if ($action->hasOptions()) {
				$form->addSelect($id, '', $action->getOptions())
					->setAttribute('id', static::ID_ATTRIBUTE_PREFIX . $id);
			}
		}

		foreach ($this->group_actions as $id => $action) {
			$form['group_action']->addCondition(Form::EQUAL, $id)
				->toggle(static::ID_ATTRIBUTE_PREFIX . $id);
		}

		$form['group_action']->addCondition(Form::FILLED)
			->toggle('group_action_submit');

		$form->addSubmit('submit', 'Provést')
			->setAttribute('id', 'group_action_submit');

		$form->onSubmit[] = [$this, 'submitted'];

		return $form;
	}


	/**
	 * Pass "sub"-form submission forward to custom submit function
	 * @param  Form   $form
	 * @return void
	 */
	public function submitted(Form $form)
	{
		$values = $form->getValues();

		if ($values->group_action === 0) {
			return;
		}

		/**
		 * @todo Define items IDs
		 */
		$http_ids = $form->getHttpData(Form::DATA_LINE|Form::DATA_KEYS, 'group_action_item[]');
		$ids = array_keys($http_ids);

		$id = $values->group_action;
		$this->group_actions[$id]->onSelect($ids, isset($values->{$id}) ? $values->{$id} : NULL);
	}


	/**
	 * Add one group action to collection of actions
	 * @param string $title
	 * @param array $options
	 */
	public function addGroupAction($title, $options)
	{
		$id = ($s = sizeof($this->group_actions)) ? ($s + 1) : 1;

		return $this->group_actions[$id] = new GroupAction($title, $options);
	}

}
