<?php declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Utils;


final class Sorting
{

	/**
	 * @var array
	 */
	private $sort = [];

	/**
	 * @var callable|null
	 */
	private $sortCallback = null;


	public function __construct(array $sort, callable $sortCallback = null)
	{
		$this->sort = $sort;
		$this->sortCallback = $sortCallback;
	}


	public function getSort(): array
	{
		return $this->sort;
	}


	public function getSortCallback(): ?callable
	{
		return $this->sortCallback;
	}
}
