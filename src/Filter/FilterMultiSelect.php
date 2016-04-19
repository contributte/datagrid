<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Filter;

use Nette;

class FilterMultiSelect extends FilterSelect
{

	/**
	 * @var string
	 */
	protected $type = 'multi-select';


	/**
	 * Get filter condition
	 * @return array
	 */
	public function getCondition()
	{
		$return = [$this->column => []];

		foreach ($this->getValue() as $value) {
			$return[$this->column][] = $value;
		}

		return $return;
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
		return $container->addMultiSelect($key, $name, $options);
	}

}
