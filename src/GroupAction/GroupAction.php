<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\GroupAction;

use Nette;

/**
 * @method void onSelect(array $ids, string $value)
 */
abstract class GroupAction extends Nette\Object
{
	/**
	 * @var callable[]
	 */
	public $onSelect = [];

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @param string $title
	 */
	public function __construct($title)
	{
		$this->title = $title;
	}

	/**
	 * Get action title
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}
}
