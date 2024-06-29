<?php declare(strict_types = 1);

namespace Contributte\Datagrid\GroupAction;

use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Exception\DatagridGroupActionException;
use Nette\Application\UI\Form;
use Nette\Forms\Container;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form as NetteForm;
use UnexpectedValueException;

class GroupActionCollection
{

	private const ID_ATTRIBUTE_PREFIX = '_item_';

	/** @var array<GroupAction> */
	protected array $groupActions = [];

	public function __construct(protected Datagrid $datagrid)
	{
	}

	public function addToFormContainer(Container $container): void
	{
		/** @var Form $form */
		$form = $container->lookup(Form::class);
		$lookupPath = $container->lookupPath();
		$translator = $form->getTranslator();
		$main_options = [];

		if ($translator === null) {
			throw new UnexpectedValueException();
		}

		/**
		 * First foreach for adding button actions
		 */
		foreach ($this->groupActions as $id => $action) {
			if ($action instanceof GroupButtonAction) {
				$control = $container->addSubmit((string) $id, $action->getTitle());

				/**
				 * User may set a class to the form control
				 */
				$control->setHtmlAttribute('class', $action->getClass());

				/**
				 * User may set additional attribtues to the form control
				 */
				foreach ($action->getAttributes() as $name => $value) {
					$control->setHtmlAttribute($name, $value);
				}
			}
		}

		/**
		 * Second foreach for filling "main" select
		 */
		foreach ($this->groupActions as $id => $action) {
			if (! $action instanceof GroupButtonAction) {
				$main_options[$id] = $action->getTitle();
			}
		}

		$groupActionSelect = $container->addSelect('group_action', '', $main_options)
			->setPrompt('contributte_datagrid.choose');

		/**
		 * Third for creating select for each "sub"-action
		 */
		foreach ($this->groupActions as $id => $action) {
			$control = null;

			if ($action instanceof GroupSelectAction) {
				if ($action->hasOptions()) {
					if ($action instanceof GroupMultiSelectAction) {
						$control = $container->addMultiSelect((string) $id, '', $action->getOptions());
						$control->setHtmlAttribute('data-datagrid-multiselect-id', $lookupPath . self::ID_ATTRIBUTE_PREFIX . $id);
						$control->setHtmlAttribute('data-style', 'hidden');
						$control->setHtmlAttribute('data-selected-icon-check', Datagrid::$iconPrefix . 'check');
					} else {
						$control = $container->addSelect((string) $id, '', $action->getOptions());
					}

					$control->setHtmlAttribute('id', $lookupPath . self::ID_ATTRIBUTE_PREFIX . $id);
				}
			} elseif ($action instanceof GroupTextAction) {
				$control = $container->addText((string) $id, '');

				$control->setHtmlAttribute('id', $lookupPath . self::ID_ATTRIBUTE_PREFIX . $id)
					->addConditionOn($groupActionSelect, Form::Equal, $id)
					->setRequired('contributte_datagrid.choose_input_required')
					->endCondition();

			} elseif ($action instanceof GroupTextareaAction) {
				$control = $container->addTextArea((string) $id, '');

				$control->setHtmlAttribute('id', $lookupPath . self::ID_ATTRIBUTE_PREFIX . $id)
					->addConditionOn($groupActionSelect, Form::Equal, $id)
					->setRequired('contributte_datagrid.choose_input_required');
			}

			if (isset($control)) {
				/**
				 * User may set a class to the form control
				 */
				$control->setHtmlAttribute('class', $action->getClass());

				/**
				 * User may set additional attribtues to the form control
				 */
				foreach ($action->getAttributes() as $name => $value) {
					$control->setHtmlAttribute($name, $value);
				}
			}
		}

		if ($main_options !== []) {
			foreach (array_keys($this->groupActions) as $id) {
				$groupActionSelect->addCondition(Form::Equal, $id)
					->toggle($lookupPath . self::ID_ATTRIBUTE_PREFIX . $id);
			}

			$groupActionSelect->addCondition(Form::Filled)
				->toggle(
					strtolower($this->datagrid->getFullName()) . 'group_action_submit'
				);

			$container->addSubmit('submit', 'contributte_datagrid.execute')
				->setValidationScope([$container])
				->setHtmlAttribute(
					'id',
					strtolower($this->datagrid->getFullName()) . 'group_action_submit'
				);
		} else {
			unset($container['group_action']);
		}

		$form->onSubmit[] = function (NetteForm $form): void {
			$this->submitted($form);
		};
	}

	/**
	 * Pass "sub"-form submission forward to custom submit function
	 */
	public function submitted(NetteForm $form): void
	{
		$submitter = $this->getFormSubmitter($form);

		if (! $submitter instanceof SubmitButton) {
			return;
		}

		$values = (array) $form->getValues();
		$values = $values['group_action'];

		if (
			($submitter->getName() === 'submit' && $submitter->isSubmittedBy())
			 && ($values->group_action === 0 || $values->group_action === null)) {
			return;
		}

		/** @var array $httpIds */
		$httpIds = $form->getHttpData(
			Form::DataLine | Form::DataKeys,
			strtolower($this->datagrid->getFullName()) . '_group_action_item[]'
		);

		$ids = array_keys($httpIds);

		if ($submitter->getName() === 'submit') {
			$id = $values->group_action;
			$this->groupActions[$id]->onSelect($ids, $values[$id] ?? null);

			if (!$form['group_action'] instanceof Container) {
				throw new UnexpectedValueException();
			}

			if (isset($form['group_action']['group_action'])) {
				if (!$form['group_action']['group_action'] instanceof SelectBox) {
					throw new UnexpectedValueException();
				}

				$form['group_action']['group_action']->setValue(null);
			}
		} else {
			$groupButtonAction = $this->groupActions[$submitter->getName()];

			if (!$groupButtonAction instanceof GroupButtonAction) {
				throw new UnexpectedValueException('This action is supposed to be a GroupButtonAction');
			}

			$groupButtonAction->onClick($ids);
		}
	}

	/**
	 * Add one group button action to collection of actions
	 */
	public function addGroupButtonAction(string $title, ?string $class = null): GroupButtonAction
	{
		$id = count($this->groupActions) > 0 ? count($this->groupActions) + 1 : 1;

		return $this->groupActions[$id] = new GroupButtonAction($title, $class);
	}

	/**
	 * Add one group action (select box) to collection of actions
	 */
	public function addGroupSelectAction(string $title, array $options): GroupAction
	{
		$id = count($this->groupActions) > 0 ? count($this->groupActions) + 1 : 1;

		return $this->groupActions[$id] = new GroupSelectAction($title, $options);
	}

	/**
	 * Add one group action (multiselect box) to collection of actions
	 */
	public function addGroupMultiSelectAction(string $title, array $options): GroupAction
	{
		$id = count($this->groupActions) > 0 ? count($this->groupActions) + 1 : 1;

		return $this->groupActions[$id] = new GroupMultiSelectAction($title, $options);
	}

	/**
	 * Add one group action (text input) to collection of actions
	 */
	public function addGroupTextAction(string $title): GroupAction
	{
		$id = count($this->groupActions) > 0 ? count($this->groupActions) + 1 : 1;

		return $this->groupActions[$id] = new GroupTextAction($title);
	}

	/**
	 * Add one group action (textarea) to collection of actions
	 */
	public function addGroupTextareaAction(string $title): GroupAction
	{
		$id = count($this->groupActions) > 0 ? count($this->groupActions) + 1 : 1;

		return $this->groupActions[$id] = new GroupTextareaAction($title);
	}

	public function getGroupAction(string $title): GroupAction
	{
		foreach ($this->groupActions as $action) {
			if ($action->getTitle() === $title) {
				return $action;
			}
		}

		throw new DatagridGroupActionException(sprintf('Group action %s does not exist.', $title));
	}

	private function getFormSubmitter(NetteForm $form): ?SubmitButton
	{
		$container = $form['group_action'];

		if (!$container instanceof Container) {
			throw new UnexpectedValueException();
		}

		if (isset($container['submit'])) {
			if (!$container['submit'] instanceof SubmitButton) {
				throw new UnexpectedValueException();
			}

			if ($container['submit']->isSubmittedBy()) {
				return $container['submit'];
			}
		}

		foreach ($container->getComponents() as $component) {
			if ($component instanceof SubmitButton && $component->isSubmittedBy()) {
				return $component;
			}
		}

		return null;
	}

}
