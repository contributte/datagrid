<?php

/**
 * Nette Framework Extras
 *
 * This source file is subject to the New BSD License.
 *
 * For more information please see http://addons.nette.org
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2009 David Grudl
 * @license    New BSD License
 * @link       http://addons.nette.org
 * @package    Nette Extras
 */

namespace Ublaboo\DataGrid\Components\DataGridPaginator;

use Nette;
use Nette\ComponentModel\IContainer;
use Ublaboo\DataGrid\DataGrid;

/**
 * Visual paginator control.
 *
 * @property-read Nette\Application\UI\ITemplate $template
 */
class DataGridPaginator extends Nette\Application\UI\Control
{

	/**
	 * @var Nette\Localization\ITranslator
	 */
	private $translator;

	/**
	 * @var  string
	 */
	private $icon_prefix;


	/**
	 * @var Nette\Utils\Paginator
	 */
	private $paginator;

	/**
	 * @var string
	 */
	private $template_file;


	public function __construct(
		Nette\Localization\ITranslator $translator,
		$icon_prefix = 'fa fa-',
		IContainer $parent = null,
		$name = null
	) {
		parent::__construct($parent, $name);

		$this->translator = $translator;
		$this->icon_prefix = $icon_prefix;
	}


	public function setTemplateFile($template_file)
	{
		$this->template_file = (string) $template_file;
	}


	public function getTemplateFile()
	{
		return $this->template_file ?: __DIR__ . '/templates/data_grid_paginator.latte';
	}


	/**
	 * Get paginator original template file
	 * @return string
	 */
	public function getOriginalTemplateFile()
	{
		return __DIR__ . '/templates/data_grid_paginator.latte';
	}


	/**
	 * @return Nette\Utils\Paginator
	 */
	public function getPaginator()
	{
		if (!$this->paginator) {
			$this->paginator = new Nette\Utils\Paginator;
		}

		return $this->paginator;
	}


	/**
	 * Renders paginator.
	 * @return void
	 */
	public function render()
	{
		$paginator = $this->getPaginator();
		$page = $paginator->page;

		if ($paginator->pageCount < 2) {
			$steps = [$page];

		} else {
			$arr = range(max($paginator->firstPage, $page - 2), min($paginator->lastPage, $page + 2));

			/**
			 * Something to do with steps in tempale...
			 * [Default $count = 3;]
			 * @var int
			 */
			$count = 1;

			$quotient = ($paginator->pageCount - 1) / $count;
			for ($i = 0; $i <= $count; $i++) {
				$arr[] = round($quotient * $i) + $paginator->firstPage;
			}
			sort($arr);
			$steps = array_values(array_unique($arr));
		}

		$this->template->setTranslator($this->translator);

		if (!isset($this->template->steps)) {
			$this->template->add('steps', $steps);
		}

		if (!isset($this->template->paginator)) {
			$this->template->add('paginator', $paginator);
		}

		//$this->template->add('icon_prefix', $this->icon_prefix);
		$this->template->icon_prefix = $this->icon_prefix;
		//$this->template->add('original_template', $this->getOriginalTemplateFile());
		$this->template->original_template = $this->getOriginalTemplateFile();
		$this->template->setFile($this->getTemplateFile());
		$this->template->render();
	}


	/**
	 * Loads state informations.
	 * @param  array
	 * @return void
	 */
	public function loadState(array $params)
	{
		parent::loadState($params);

		if ($this->getParent() instanceof DataGrid) {
			$this->getPaginator()->page = $this->getParent()->page;
		}
	}
}
