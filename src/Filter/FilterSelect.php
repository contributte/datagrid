<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Filter;

use Nette;
use Ublaboo\DataGrid\DataGrid;

class FilterSelect extends Filter
{

	/**
	 * @var array
	 */
	protected $options;

	/**
	 * @var bool
	 */
	protected $translateOptions = false;

	/**
	 * @var string
	 */
	protected $template = 'datagrid_filter_select.latte';

	/**
	 * @var string
	 */
	protected $type = 'select';

	/**
	 * @var string|NULL
	 */
	protected $prompt = null;


	/**
	 * @param DataGrid $grid
	 * @param string   $key
	 * @param string   $name
	 * @param string   $options
	 * @param string   $column
	 */
	public function __construct($grid, $key, $name, array $options, $column)
	{
		parent::__construct($grid, $key, $name, $column);

		$this->options = $options;
	}


	/**
	 * Adds select box to filter form
	 * @param Nette\Forms\Container $container
	 */
	public function addToFormContainer(Nette\Forms\Container $container)
	{
		$form = $container->lookup('Nette\Application\UI\Form');
		$translator = $form->getTranslator();

		if (!$this->translateOptions) {
			$select = $this->addControl(
				$container,
				$this->key,
				$translator->translate($this->name),
				$this->options
			);

			$select->setTranslator(null);
		} else {
			$select = $this->addControl($container, $this->key, $this->name, $this->options);
		}
	}


	/**
	 * @param  bool  $translateOptions
	 * @return static
	 */
	public function setTranslateOptions($translateOptions = true)
	{
		$this->translateOptions = (bool) $translateOptions;
		return $this;
	}


	/**
	 * @return bool
	 */
	public function getTranslateOptions()
	{
		return $this->translateOptions;
	}


	/**
	 * Get filter condition
	 * @return array
	 */
	public function getCondition()
	{
		return [$this->column => $this->getValue()];
	}


	/**
	 * Get filter prompt
	 * @return string|NULL
	 */
	public function getPrompt()
	{
		return $this->prompt;
	}


	/**
	 * Set filter prompt value
	 * @param string|NULL $prompt
	 * @return static
	 */
	public function setPrompt($prompt)
	{
		$this->prompt = $prompt;
		return $this;
	}


	/**
	 * Tell if prompt has been set in this fitler
	 * @return bool
	 */
	public function isPromptEnabled()
	{
		return isset($this->prompt);
	}


	/**
	 * @param Nette\Forms\Container $container
	 * @param string                $key
	 * @param string                $name
	 * @param array                $options
	 * @return Nette\Forms\Controls\SelectBox
	 */
	protected function addControl(Nette\Forms\Container $container, $key, $name, $options)
	{
		$input = $container->addSelect($key, $name, $options);

		if ($this->isPromptEnabled()) {
			$input->setPrompt($this->prompt);
		}

		return $this->addAttributes($input);
	}
}
