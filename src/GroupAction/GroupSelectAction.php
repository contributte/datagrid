<?php declare(strict_types = 1);

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
	 * @param array  $options
	 */
	public function __construct(string $title, ?array $options = null)
	{
		parent::__construct($title);
		$this->options = $options;
	}


	/**
	 * Get action options
	 *
	 * @return array
	 */
	public function getOptions(): array
	{
		return $this->options;
	}


	/**
	 * Has the action some options?
	 */
	public function hasOptions(): bool
	{
		return (bool) $this->options;
	}

}
