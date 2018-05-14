<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid;

use Dibi\Row as DibiRow;
use LeanMapper;
use Nette;
use Nette\Database\Table\ActiveRow;
use Nette\SmartObject;
use Nette\Utils\Html;
use Nextras;
use Ublaboo\DataGrid\Exception\DataGridException;
use Ublaboo\DataGrid\Utils\PropertyAccessHelper;

class Row
{

	use SmartObject;

	/**
	 * @var DataGrid
	 */
	protected $datagrid;

	/**
	 * @var mixed
	 */
	protected $item;

	/**
	 * @var string
	 */
	protected $primary_key;

	/**
	 * @var mixed
	 */
	protected $id;

	/**
	 * @var Html
	 */
	protected $control;

/**
 * @param mixed    $item
 * @param string   $primary_key
 */
	public function __construct(DataGrid $datagrid, $item, $primary_key)
	{
		$this->control = Html::el('tr');
		$this->datagrid = $datagrid;
		$this->item = $item;
		$this->primary_key = $primary_key;
		$this->id = $this->getValue($primary_key);

		if ($datagrid->hasColumnsSummary()) {
			$datagrid->getColumnsSummary()->add($this);
		}
	}


	/**
	 * Get id value of item
	 *
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}


	/**
	 * Get item value of key
	 *
	 * @param  mixed $key
	 * @return mixed
	 */
	public function getValue($key)
	{
		if ($this->item instanceof LeanMapper\Entity) {
			return $this->getLeanMapperEntityProperty($this->item, $key);

		} elseif ($this->item instanceof Nextras\Orm\Entity\Entity) {
			return $this->getNextrasEntityProperty($this->item, $key);

		} elseif ($this->item instanceof DibiRow) {
			return $this->item->{$this->formatDibiRowKey($key)};

		} elseif ($this->item instanceof ActiveRow) {
			return $this->getActiveRowProperty($this->item, $key);

		} elseif ($this->item instanceof Nette\Database\Row) {
			return $this->item->{$key};

		} elseif (is_array($this->item)) {
			return $this->item[$key];

		} else {
			/**
			 * Doctrine entity
			 */
			return $this->getDoctrineEntityProperty($this->item, $key);
		}
	}


	public function getControl(): Html
	{
		return $this->control;
	}


	public function getControlClass(): string
	{
		if (!$class = $this->control->class) {
			return '';
		}

		return implode(' ', array_keys($class));
	}


	/**
	 * @return mixed|NULL
	 */
	public function getActiveRowProperty(ActiveRow $item, string $key)
	{
		if (preg_match('/^:([a-zA-Z0-9_$]+)\.([a-zA-Z0-9_$]+)(:([a-zA-Z0-9_$]+))?$/', $key, $matches)) {
			$relatedTable = $matches[1];
			$relatedColumn = $matches[2];
			$throughColumn = $matches[4] ?? null;

			$relatedRow = $item->related($relatedTable, $throughColumn)->fetch();

			return $relatedRow ? $relatedRow->{$relatedColumn} : null;
		}

		if (preg_match('/^([a-zA-Z0-9_$]+)\.([a-zA-Z0-9_$]+)(:([a-zA-Z0-9_$]+))?$/', $key, $matches)) {
			$referencedTable = $matches[1];
			$referencedColumn = $matches[2];
			$throughColumn = $matches[4] ?? null;

			$referencedRow = $item->ref($referencedTable, $throughColumn);

			return $referencedRow ? $referencedRow->{$referencedColumn} : null;
		}

		return $item->{$key};
	}


	/**
	 * LeanMapper: Access object properties to get a item value
	 *
	 * @param  mixed             $key
	 * @return mixed
	 */
	public function getLeanMapperEntityProperty(LeanMapper\Entity $item, $key)
	{
		$properties = explode('.', $key);
		$value = $item;

		while ($property = array_shift($properties)) {
			if (!isset($value->{$property})) {
				if ($this->datagrid->strict_entity_property) {
					throw new DataGridException(sprintf(
						'Target Property [%s] is not an object or is empty, trying to get [%s]',
						$value,
						str_replace('.', '->', $key)
					));
				}

				return null;
			}

			$value = $value->{$property};
		}

		return $value;
	}


	/**
	 * Nextras: Access object properties to get a item value
	 *
	 * @return mixed
	 */
	public function getNextrasEntityProperty(Nextras\Orm\Entity\Entity $item, string $key)
	{
		$properties = explode('.', $key);
		$value = $item;

		while ($property = array_shift($properties)) {
			if (!isset($value->{$property})) {
				if ($this->datagrid->strict_entity_property) {
					throw new DataGridException(sprintf(
						'Target Property [%s] is not an object or is empty, trying to get [%s]',
						$value,
						str_replace('.', '->', $key)
					));
				}

				return null;
			}

			$value = $value->{$property};
		}

		return $value;
	}


	/**
	 * Doctrine: Access object properties to get a item value
	 *
	 * @param  mixed $item
	 * @param  mixed $key
	 * @return mixed
	 */
	public function getDoctrineEntityProperty($item, $key)
	{
		$properties = explode('.', $key);
		$value = $item;
		$accessor = PropertyAccessHelper::getAccessor();

		while ($property = array_shift($properties)) {
			if (!is_object($value) && !$value) {
				if ($this->datagrid->strict_entity_property) {
					throw new DataGridException(sprintf(
						'Target Property [%s] is not an object or is empty, trying to get [%s]',
						$value,
						str_replace('.', '->', $key)
					));
				}

				return null;
			}

			$value = $accessor->getValue($value, $property);
		}

		return $value;
	}


	/**
	 * Get original item
	 *
	 * @return mixed
	 */
	public function getItem()
	{
		return $this->item;
	}


	/**
	 * Has particular row group actions allowed?
	 */
	public function hasGroupAction(): bool
	{
		$condition = $this->datagrid->getRowCondition('group_action');

		return $condition ? $condition($this->item) : true;
	}


	/**
	 * Has particular row a action allowed?
	 *
	 * @param  mixed  $key
	 */
	public function hasAction($key): bool
	{
		$condition = $this->datagrid->getRowCondition('action', $key);

		return $condition ? $condition($this->item) : true;
	}


	/**
	 * Has particular row inlie edit allowed?
	 */
	public function hasInlineEdit(): bool
	{
		$condition = $this->datagrid->getRowCondition('inline_edit');

		return $condition ? $condition($this->item) : true;
	}


	public function applyColumnCallback(string $key, Column\Column $column): Column\Column
	{
		$callback = $this->datagrid->getColumnCallback($key);

		if ($callback !== null) {
			call_user_func($callback, $column, $this->getItem());
		}

		return $column;
	}


	/**
	 * Key may contain ".", get rid of it (+ the table alias)
	 */
	private function formatDibiRowKey(string $key): string
	{
		if ($offset = strpos($key, '.')) {
			return substr($key, $offset + 1);
		}

		return $key;
	}

}
