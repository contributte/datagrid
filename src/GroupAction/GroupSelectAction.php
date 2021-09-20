<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\GroupAction;

/**
 * @method void onSelect()
 */
class GroupSelectAction extends GroupAction
{

	/**
	 * @var array
	 */
	protected $options;


	/**
	 * @param string $title
	 * @param array  $options
	 */
	public function __construct($title, $options = null)
	{
		parent::__construct($title);
		$this->options = $options;
	}


	/**
	 * Get action options
	 * @return array
	 */
	public function getOptions()
	{
		return $this->options;
	}


	/**
	 * Has the action some options?
	 * @return boolean
	 */
	public function hasOptions()
	{
		return (bool) $this->options;
	}
}
