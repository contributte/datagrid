<?php declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Column;

use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridColumnRendererException;
use Ublaboo\DataGrid\Exception\DataGridException;
use Ublaboo\DataGrid\Row;
use Ublaboo\DataGrid\Traits;
use Ublaboo\DataGrid\Traits\TButtonText;
use Ublaboo\DataGrid\Traits\TButtonTryAddIcon;
use Ublaboo\DataGrid\Traits\TLink;
use Ublaboo\DataGrid\Traits\TRenderCondition;

class Action extends Column
{

	use TButtonTryAddIcon;
	use TButtonText;
	use TLink;
	use TRenderCondition;

	/**
	 * @var string
	 */
	public static $dataConfirmAttributeName = 'datagrid-confirm';

	/**
	 * @var DataGrid
	 */
	protected $grid;

	/**
	 * @var string
	 */
	protected $href;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var array
	 */
	protected $params;

	/**
	 * @var array|callable
	 */
	protected $confirm;

	/**
	 * @var array
	 */
	protected $dataAttributes = [];

	/**
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * @var array
	 */
	protected $parameters = [];

	/**
	 * @var string|callable
	 */
	protected $icon;

	/**
	 * @var string|callable
	 */
	protected $class = 'btn btn-xs btn-default btn-secondary';

	/**
	 * @var bool
	 */
	protected $openInNewTab = false;

	/**
	 * @var string|callable
	 */
	private $title;


	public function __construct(DataGrid $grid, string $href, string $name, array $params)
	{
		$this->grid = $grid;
		$this->href = $href;
		$this->name = $name;
		$this->params = $params;
	}


	/**
	 * @param  Row $row
	 * @return mixed
	 */
	public function render(Row $row)
	{
		if (!$this->shouldBeRendered($row)) {
			return null;
		}

		try {
			return $this->useRenderer($row);
		} catch (DataGridColumnRendererException $e) {
		}

		$link = $this->createLink(
			$this->grid,
			$this->href,
			$this->getItemParams($row, $this->params) + $this->parameters
		);

		$a = Html::el('a')->href($link);

		$this->tryAddIcon($a, $this->getIcon($row), $this->getName());

		if (!empty($this->dataAttributes)) {
			foreach ($this->dataAttributes as $key => $value) {
				$a->data($key, $value);
			}
		}

		if (!empty($this->attributes)) {
			$a->addAttributes($this->attributes);
		}

		$a->addText($this->translate($this->getName()));

		if ($this->title) {
			$a->title($this->translate($this->getTitle($row)));
		}

		if ($this->class) {
			$a->class($this->getClass($row));
		}

		if ($confirm = $this->getConfirm($row)) {
			$a->data(static::$dataConfirmAttributeName, $confirm);
		}

		if ($this->openInNewTab) {
			$a->addAttributes(['target' => '_blank']);
		}

		return $a;
	}


	public function addParameters(array $parameters): self
	{
		$this->parameters = $parameters;

		return $this;
	}


	/**
	 * @param string|callable $title
	 * @throws DataGridException
	 */
	public function setTitle($title): self
	{
		$this->checkPropertyStringOrCallable($title, 'title');

		$this->title = $title;

		return $this;
	}


	/**
	 * @throws DataGridException
	 */
	public function getTitle(Row $row): string
	{
		/**
		 * If user callback was used for setting action title, it has to return string
		 */
		return $this->getPropertyStringOrCallableGetString($row, $this->title, 'title');
	}


	/**
	 * @param string|callable $class
	 * @throws DataGridException
	 */
	public function setClass($class): self
	{
		$this->checkPropertyStringOrCallable($class, 'class');

		$this->class = $class;

		return $this;
	}


	/**
	 * @throws DataGridException
	 */
	public function getClass(Row $row): string
	{
		/**
		 * If user callback was used for setting action class, it has to return string
		 */
		return $this->getPropertyStringOrCallableGetString($row, $this->class, 'class');
	}


	/**
	 * @param string|callable $icon
	 * @throws DataGridException
	 */
	public function setIcon($icon): self
	{
		$this->checkPropertyStringOrCallable($icon, 'icon');

		$this->icon = $icon;

		return $this;
	}


	/**
	 * @throws DataGridException
	 */
	public function getIcon(Row $row): string
	{
		/**
		 * If user callback was used for setting action icon, it has to return string
		 */
		return $this->getPropertyStringOrCallableGetString($row, $this->icon, 'icon');
	}


	/**
	 * @param string|callable $message
	 * @throws DataGridException
	 */
	public function setConfirm($message, $column = null): self
	{
		$this->checkPropertyStringOrCallable($message, 'confirmation message');

		$this->confirm = [$message, $column];

		return $this;
	}


	/**
	 * @throws DataGridException
	 */
	public function getConfirm(Row $row): string
	{
		if (!$this->confirm) {
			return null;
		}

		$question = $this->confirm[0];

		if (is_string($question)) {
			$question = $this->translate($question);
		} else {
			/**
			 * If user callback was used for setting action confirmation dialog, it has to return string
			 */
			$question = $this->getPropertyStringOrCallableGetString($row, $question, 'confirmation dialog');
		}

		if (!$this->confirm[1]) {
			return $question;
		}

		return str_replace('%s', $row->getValue($this->confirm[1]), $question);
	}


	/**
	 * @param mixed $value
	 */
	public function setDataAttribute(string $key, $value): self
	{
		$this->dataAttributes[$key] = $value;

		return $this;
	}


	public function addAttributes(array $attrs): self
	{
		$this->attributes = $this->attributes + $attrs;

		return $this;
	}


	/**
	 * @param  mixed $property
	 * @throws DataGridException
	 */
	protected function checkPropertyStringOrCallable($property, $name): void
	{
		if (!is_string($property) && !is_callable($property) && $property !== null) {
			throw new DataGridException(
				"Action {$name} has to be either string or a callback returning string"
			);
		}
	}


	/**
	 * @param  string|callable|null $property
	 * @throws DataGridException
	 */
	public function getPropertyStringOrCallableGetString(Row $row, $property, string $name): string
	{
		if (is_string($property)) {
			return $property;
		}

		if (is_callable($property)) {
			$value = call_user_func($property, $row->getItem());

			if (!is_string($value)) {
				throw new DataGridException("Action {$name} callback has to return a string");
			}

			return $value;
		}

		return $property;
	}


	protected function translate(string $message): string
	{
		return $this->grid->getTranslator()->translate($message);
	}


	public function isOpenInNewTab(): bool
	{
		return $this->openInNewTab;
	}


	public function setOpenInNewTab(bool $openInNewTab = true): self
	{
		$this->openInNewTab = $openInNewTab;

		return $this;
	}
}
