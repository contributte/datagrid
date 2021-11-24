<?php

namespace Ublaboo\DataGrid\Utils;

class IconData
{
	/**
	 * @var string
	 */
	public $content;

	/**
	 * @var string
	 */
	public $iconClass;


	/**
	 * @param string  $content
	 * @param string  $iconClass
	 */
	public function __construct($content, $iconClass)
	{
		$this->content = $content;
		$this->iconClass = $iconClass;
	}
}
