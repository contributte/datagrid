<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid;


use Nette\Http\SessionSection;

interface UIStorage {
	
	public function saveState(string $gridName, string $key, $value) : void;
	
	public function flushState(string $gridName, string $key) : void;
	
	public function getState(string $gridName, ?string $key = null, $defaultValue = null);
}