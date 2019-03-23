<?php declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Filter;

use Nette;
use Nette\Forms\Container;
use Nette\Forms\Controls\SelectBox;
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
	 * @var string|null
	 */
	protected $prompt = null;


	public function __construct(
		DataGrid $grid,
		string $key,
		string $name,
		array $options,
		string $column
	) {
		parent::__construct($grid, $key, $name, $column);

		$this->options = $options;
	}


	public function addToFormContainer(Container $container): void
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


	public function setTranslateOptions(bool $translateOptions = true): self
	{
		$this->translateOptions = (bool) $translateOptions;
		return $this;
	}


	public function getOptions(): array
	{
		return $this->options;
	}


	public function getTranslateOptions(): bool
	{
		return $this->translateOptions;
	}


	public function getCondition(): array
	{
		return [$this->column => $this->getValue()];
	}


	public function getPrompt(): ?string
	{
		return $this->prompt;
	}


	public function setPrompt(?string $prompt): self
	{
		$this->prompt = $prompt;

		return $this;
	}


	/**
	 * Tell if prompt has been set in this fitler
	 */
	public function isPromptEnabled(): bool
	{
		return isset($this->prompt);
	}


	protected function addControl(
		Container $container,
		string $key,
		string $name,
		array $options
	): SelectBox
	{
		$input = $container->addSelect($key, $name, $options);

		if ($this->isPromptEnabled()) {
			$input->setPrompt($this->prompt);
		}

		return $this->addAttributes($input);
	}
}
