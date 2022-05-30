<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\Utils;

use Nette\Application\UI\Presenter;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Container;
use Nette\Utils\Arrays;
use Traversable;
use UnexpectedValueException;

final class ItemDetailForm extends Container
{

	/**
	 * @var callable
	 */
	private $callableSetContainer;

	/**
	 * @var ?array
	 */
	private $httpPost;

	/**
	 * @var array<bool>
	 */
	private $containerSetByName = [];


	public function __construct(callable $callableSetContainer)
	{
		$this->monitor(
			Presenter::class,
			function(Presenter $presenter): void {
				$this->loadHttpData();
			}
		);

		$this->callableSetContainer = $callableSetContainer;
	}


	/**
	 * @param mixed $name
	 */
	public function offsetGet($name): IComponent
	{
		return $this->getComponentAndSetContainer($name);
	}


	/**
	 * @param mixed $name
	 */
	public function getComponentAndSetContainer($name): IComponent
	{
		$container = $this->addContainer($name);

		if (!isset($this->containerSetByName[$name])) {
			call_user_func($this->callableSetContainer, $container);

			$this->containerSetByName[$name] = true;
		}

		return $container;
	}


	/**
	 * @return mixed|null
	 * @throws UnexpectedValueException
	 */
	private function getHttpData()
	{
		if ($this->httpPost === null) {
			$lookupPath = $this->lookupPath('Nette\Forms\Form');
			$form = $this->getForm();

			if ($lookupPath === null || $form === null) {
				throw new UnexpectedValueException;
			}

			$path = explode(self::NAME_SEPARATOR, $lookupPath);

			$this->httpPost = Arrays::get($form->getHttpData(), $path, null);
		}

		return $this->httpPost;
	}


	/**
	 * @throws UnexpectedValueException
	 */
	private function loadHttpData(): void
	{
		$form = $this->getForm();

		if ($form === null) {
			throw new UnexpectedValueException;
		}

		if ($form->isSubmitted() === false) {
			return;
		}

		foreach ((array) $this->getHttpData() as $name => $value) {
			if ((is_array($value) || $value instanceof Traversable)) {
				$this->getComponentAndSetContainer($name);
			}
		}
	}
}
