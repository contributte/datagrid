<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Utils;

use Nette\SmartObject;

final class Sorting
{

	use SmartObject;

	/**
	 * @var array
	 */
	private $sort = [];

	/**
	 * @var callable|null
	 */
	private $sort_callback = null;

	public function __construct(array $sort, ?callable $sort_callback = null)
	{
		$this->sort = $sort;
		$this->sort_callback = $sort_callback;
	}


	/**
	 * @return array
	 */
	public function getSort(): array
	{
		return $this->sort;
	}


	public function getSortCallback(): ?callable
	{
		return $this->sort_callback;
	}

}
