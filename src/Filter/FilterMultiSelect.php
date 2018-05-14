<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Filter;

use Nette;
use Ublaboo\DataGrid\DataGrid;

class FilterMultiSelect extends FilterSelect
{

	/**
	 * @var string
	 */
	protected $type = 'multi-select';

	/**
	 * @var array
	 */
	protected $attributes = [
		'class' => ['form-control', 'input-sm', 'selectpicker'],
		'data-selected-text-format' => ['count'],
	];

/**
 * @param string   $options
 */
	public function __construct(DataGrid $grid, string $key, string $name, array $options, string $column)
	{
		parent::__construct($grid, $key, $name, $options, $column);

		$this->addAttribute('data-selected-icon-check', DataGrid::$icon_prefix . 'check');
	}


	/**
	 * Get filter condition
	 *
	 * @return array
	 */
	public function getCondition(): array
	{
		$return = [$this->column => []];

		foreach ($this->getValue() as $value) {
			$return[$this->column][] = $value;
		}

		return $return;
	}


	/**
	 * @param array                $options
	 */
	protected function addControl(Nette\Forms\Container $container, string $key, string $name, array $options): Nette\Forms\Controls\SelectBox
	{
		/**
		 * Set some translated texts
		 */
		$form = $container->lookup('Nette\Application\UI\Form');
		$t = [$form->getTranslator(), 'translate'];

		$this->addAttribute('title', $t('ublaboo_datagrid.multiselect_choose'));
		$this->addAttribute('data-i18n-selected', $t('ublaboo_datagrid.multiselect_selected'));

		/**
		 * Add input to container
		 */
		$input = $container->addMultiSelect($key, $name, $options);

		return $this->addAttributes($input);
	}

}
