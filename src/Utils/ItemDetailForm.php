<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Utils;

use Nette\Application\UI\Presenter;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Container;
use Nette\Forms\Form;
use Nette\Utils\Arrays;
use UnexpectedValueException;

final class ItemDetailForm extends Container
{

	/** @var callable */
	private $callableSetContainer;

	/** @var ?array */
	private ?array $httpPost = null;

	/** @var array<bool> */
	private array $containerSetByName = [];

	public function __construct(callable $callableSetContainer)
	{
		$this->monitor(
			Presenter::class,
			function (Presenter $presenter): void {
				$this->loadHttpData();
			}
		);

		$this->callableSetContainer = $callableSetContainer;
	}

	public function offsetGet(mixed $name): IComponent
	{
		return $this->getComponentAndSetContainer($name);
	}

	public function getComponentAndSetContainer(mixed $name): IComponent
	{
		$container = $this->addContainer($name);

		if (!isset($this->containerSetByName[$name])) {
			call_user_func($this->callableSetContainer, $container);

			$this->containerSetByName[$name] = true;
		}

		return $container;
	}

	/**
	 * @throws UnexpectedValueException
	 */
	private function getHttpData(): mixed
	{
		if ($this->httpPost === null) {
			$lookupPath = $this->lookupPath(Form::class);
			$form = $this->getForm();

			$path = explode(self::NameSeparator, $lookupPath);

			/** @var array $httpData */
			$httpData = $form->getHttpData();
			$this->httpPost = Arrays::get($httpData, $path, null);
		}

		return $this->httpPost;
	}

	/**
	 * @throws UnexpectedValueException
	 */
	private function loadHttpData(): void
	{
		$form = $this->getForm();

		if ($form->isSubmitted() === false) {
			return;
		}

		foreach ((array) $this->getHttpData() as $name => $value) {
			if ((is_iterable($value))) {
				$this->getComponentAndSetContainer($name);
			}
		}
	}

}
