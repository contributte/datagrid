<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\Traits;

use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;

trait TButtonTryAddIcon
{

	public function tryAddIcon(Html $el, ?string $iconString, string $name): void
	{
		if ($iconString !== null) {
			$iconClass = '';

			foreach (explode(' ', $iconString) as $icon) {
				$iconClass .= ' ' . DataGrid::$iconPrefix . $icon;
			}

			$el->addHtml(Html::el('span')->setAttribute('class', trim($iconClass)));

			if (mb_strlen($name) > 1) {
				$el->addHtml('&nbsp;');
			}
		}
	}
}
