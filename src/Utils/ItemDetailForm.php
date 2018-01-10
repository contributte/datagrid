<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Utils;

use Nette;
use Nette\Forms\Container;

final class ItemDetailForm extends Container
{

	/**
	 * @var callable
	 */
	private $callable_set_container;

	/**
	 * @var array
	 */
	private $http_post;


	/**
	 * @param callable $callable_set_container
	 */
	public function __construct(callable $callable_set_container)
	{
		parent::__construct();

		$this->monitor('Nette\Application\UI\Presenter');

		$this->callable_set_container = $callable_set_container;
	}


	/**
	 * @param \Nette\ComponentModel\IContainer
	 */
	protected function attached($presenter)
	{
		parent::attached($presenter);

		if (!$presenter instanceof Nette\Application\UI\Presenter) {
			return;
		}

		$this->loadHttpData();
	}


	/**
	 * @return void
	 */
	public function loadHttpData()
	{
		if (!$this->getForm()->isSubmitted()) {
			return;
		}

		foreach ((array) $this->getHttpData() as $name => $value) {
			if ((is_array($value) || $value instanceof \Traversable) && !$this->getComponent($name, false)) {
				$this->getComponent($name);
			}
		}
	}


	/**
	 * @return mixed|NULL
	 */
	private function getHttpData()
	{
		if ($this->http_post === null) {
			$path = explode(self::NAME_SEPARATOR, $this->lookupPath('Nette\Forms\Form'));

			$this->http_post = Nette\Utils\Arrays::get($this->getForm()->getHttpData(), $path, null);
		}

		return $this->http_post;
	}


	/**
	 * @param  string $name
	 * @return Container
	 */
	public function offsetGet($name)
	{
		return $this->getComponent($name);
	}


	/**
	 * @param  string $name
	 * @return Container
	 */
	public function getComponent($name)
	{
		$container = $this->addContainer($name);

		call_user_func($this->callable_set_container, $container);

		return $container;
	}
}
