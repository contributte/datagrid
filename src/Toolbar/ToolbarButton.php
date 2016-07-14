<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Toolbar;

use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Traits;

class ToolbarButton
{

	use Traits\TButton;
	use Traits\TLink;

	/**
	 * @var DataGrid
	 */
	protected $grid;

	/**
	 * @var string
	 */
	protected $href;

	/**
	 * @var array
	 */
	protected $params;

	/**
	 * @param DataGrid $grid
	 * @param string   $href
	 * @param string   $text
	 * @param array    $params
	 */
	public function __construct(DataGrid $grid, $href, $text, $params = [])
	{
		$this->grid = $grid;
		$this->href = $href;
		$this->text = $text;
		$this->params = $params;
	}


	/**
	 * Render toolbar button
	 * @return Html
	 */
	public function renderButton()
	{
		$link = $this->createLink($this->grid, $this->href, $this->params);

		$a = Html::el('a')->href($link);

		$this->tryAddIcon($a, $this->getIcon(), $this->getText());

		$a->addText($this->text);

		if ($this->title) {
			$a->title($this->grid->getTranslator()->translate($this->title));
		}

		if ($this->class) {
			$a->class($this->class);
		}

		return $a;
	}

}
