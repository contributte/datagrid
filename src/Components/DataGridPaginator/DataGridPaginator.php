<?php

declare(strict_types=1);

/**
 * Nette Framework Extras
 *
 * This source file is subject to the New BSD License.
 *
 * For more information please see http://addons.nette.org
 *
 * @link http://addons.nette.org
 */

namespace Ublaboo\DataGrid\Components\DataGridPaginator;

use Nette\Application\UI\Control;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Localization\ITranslator;
use Nette\Utils\Paginator;
use Ublaboo\DataGrid\DataGrid;
use UnexpectedValueException;

class DataGridPaginator extends Control
{

	/**
	 * @var ITranslator
	 */
	private $translator;

	/**
	 * @var string
	 */
	private $iconPrefix;

	/**
	 * @var string
	 */
	private $btnSecondaryClass;

	/**
	 * @var Paginator
	 */
	private $paginator;

	/**
	 * @var string|null
	 */
	private $templateFile;


	public function __construct(
		ITranslator $translator,
		string $iconPrefix = 'fa fa-',
		string $btnSecondaryClass = 'btn-default btn-secondary'
	) {
		$this->translator = $translator;
		$this->iconPrefix = $iconPrefix;
		$this->btnSecondaryClass = $btnSecondaryClass;
	}


	public function setTemplateFile(string $templateFile): void
	{
		$this->templateFile = $templateFile;
	}


	public function getTemplateFile(): string
	{
		return $this->templateFile ?? __DIR__ . '/templates/data_grid_paginator.latte';
	}


	public function getOriginalTemplateFile(): string
	{
		return __DIR__ . '/templates/data_grid_paginator.latte';
	}


	public function getPaginator(): Paginator
	{
		if ($this->paginator === null) {
			$this->paginator = new Paginator();
		}

		return $this->paginator;
	}


	public function render(): void
	{
		$paginator = $this->getPaginator();
		$page = $paginator->page;

		if ($paginator->pageCount < 2) {
			$steps = [$page];

		} else {
			$arr = range(max($paginator->firstPage, $page - 2), (int) min($paginator->lastPage, $page + 2));

			/**
			 * Something to do with steps in tempale...
			 * [Default $count = 3;]
			 *
			 * @var int
			 */
			$count = 1;

			$perPage = $paginator->pageCount;

			$quotient = ($perPage - 1) / $count;

			for ($i = 0; $i <= $count; $i++) {
				$arr[] = round($quotient * $i) + $paginator->firstPage;
			}

			sort($arr);
			$steps = array_values(array_unique($arr));
		}

		if (!$this->getTemplate() instanceof Template) {
			throw new UnexpectedValueException();
		}

		$this->getTemplate()->setTranslator($this->translator);

		if (!isset($this->getTemplate()->steps)) {
			$this->getTemplate()->steps = $steps;
		}

		if (!isset($this->getTemplate()->paginator)) {
			$this->getTemplate()->paginator = $paginator;
		}

		$this->getTemplate()->iconPrefix = $this->iconPrefix;
		$this->getTemplate()->btnSecondaryClass = $this->btnSecondaryClass;
		$this->getTemplate()->originalTemplate = $this->getOriginalTemplateFile();
		$this->getTemplate()->setFile($this->getTemplateFile());
		$this->getTemplate()->render();
	}


	/**
	 * Loads state informations.
	 */
	public function loadState(array $params): void
	{
		parent::loadState($params);

		if ($this->getParent() instanceof DataGrid) {
			$this->getPaginator()->page = $this->getParent()->page;
		}
	}
}
