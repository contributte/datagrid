<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Traits;

use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;

trait TButtonTryAddIcon
{

	/**
	 * Should the element has an icon?
	 */
	public function tryAddIcon(Html $el, ?string $icon, string $name): void
	{
		if ($icon) {
			$el->addHtml(Html::el('span')->class(DataGrid::$icon_prefix . $icon));

			if (strlen($name)) {
				$el->addHtml('&nbsp;');
			}
		}
	}

}
