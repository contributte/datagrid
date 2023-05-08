<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Utils;

final class Sorting
{

	/** @var callable|null */
	private $sortCallback = null;

	/**
	 * @param mixed[]|string[] $sort
	 */
	public function __construct(private array $sort, ?callable $sortCallback = null)
	{
		$this->sortCallback = $sortCallback;
	}

	/**
	 * @return array|string[]
	 */
	public function getSort(): array
	{
		return $this->sort;
	}

	public function getSortCallback(): ?callable
	{
		return $this->sortCallback;
	}

}
