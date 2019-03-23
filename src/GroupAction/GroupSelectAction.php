<?php declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\GroupAction;

class GroupSelectAction extends GroupAction
{

	/**
	 * @var array
	 */
	protected $options;


	public function __construct(string $title, array $options = [])
	{
		parent::__construct($title);

		$this->options = $options;
	}


	public function getOptions(): array
	{
		return $this->options;
	}


	/**
	 * Has the action some options?
	 */
	public function hasOptions(): bool
	{
		return $this->options !== [];
	}
}
