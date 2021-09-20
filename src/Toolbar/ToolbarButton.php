<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Toolbar;

use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridColumnRendererException;
use Ublaboo\DataGrid\Traits;

class ToolbarButton
{
	use Traits\TButtonTryAddIcon;
	use Traits\TButtonClass;
	use Traits\TButtonIcon;
	use Traits\TButtonRenderer;
	use Traits\TButtonText;
	use Traits\TButtonTitle;
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
	 * @var array
	 */
	protected $attributes = [];


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
		try {
			// Renderer function may be used
			return $this->useRenderer();
		} catch (DataGridColumnRendererException $e) {
			// Do not use renderer
		}

		$link = $this->createLink($this->grid, $this->href, $this->params);

		$a = Html::el('a')->href($link);

		$this->tryAddIcon($a, $this->getIcon(), $this->getText());

		if (!empty($this->attributes)) {
			$a->addAttributes($this->attributes);
		}

		$a->addText($this->grid->getTranslator()->translate($this->text));

		if ($this->getTitle()) {
			$a->title($this->grid->getTranslator()->translate($this->getTitle()));
		}

		if ($this->getClass()) {
			$a->class($this->getClass());
		}

		return $a;
	}


	/**
	 * @param array $attrs
	 * @return static
	 */
	public function addAttributes(array $attrs)
	{
		$this->attributes = $this->attributes + $attrs;

		return $this;
	}
}
