<?php declare(strict_types = 1);

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
	 * @param string   $options
	 */
	public function __construct(DataGrid $grid, string $key, string $name, array $options, string $column)
	{
		parent::__construct($grid, $key, $name, $column);

		$this->options = $options;
	}


	/**
	 * Adds select box to filter form
	 */
	public function addToFormContainer(Nette\Forms\Container $container): void
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
	 * @return static
	 */
	public function setTranslateOptions(bool $translateOptions = true)
	{
		$this->translateOptions = (bool) $translateOptions;
		return $this;
	}


	public function getTranslateOptions(): bool
	{
		return $this->translateOptions;
	}


	/**
	 * Get filter condition
	 *
	 * @return array
	 */
	public function getCondition(): array
	{
		return [$this->column => $this->getValue()];
	}


	/**
	 * Get filter prompt
	 */
	public function getPrompt(): ?string
	{
		return $this->prompt;
	}


	/**
	 * Set filter prompt value
	 *
	 * @return static
	 */
	public function setPrompt(?string $prompt)
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


	/**
	 * @param array                $options
	 */
	protected function addControl(Nette\Forms\Container $container, string $key, string $name, array $options): Nette\Forms\Controls\SelectBox
	{
		$input = $container->addSelect($key, $name, $options);

		if ($this->isPromptEnabled()) {
			$input->setPrompt($this->prompt);
		}

		return $this->addAttributes($input);
	}

}
