<?php declare(strict_types = 1);

namespace Contributte\Datagrid;

use Contributte\Datagrid\Column\Column;
use Contributte\Datagrid\Exception\DatagridException;
use Contributte\Datagrid\Utils\PropertyAccessHelper;
use Dibi\Row as DibiRow;
use LeanMapper\Entity;
use Nette\Database\Row as NetteRow;
use Nette\Database\Table\ActiveRow;
use Nette\MemberAccessException;
use Nette\Utils\Html;
use Nextras\Orm\Entity\Entity as NextrasEntity;

class Row
{

	protected mixed $id;

	protected Html $control;

	/** @var \Closure(mixed): mixed */
	private \Closure $valueAccessor;

	public function __construct(protected Datagrid $datagrid, protected mixed $item, protected string $primaryKey)
	{
		$this->control = Html::el('tr');
		$this->valueAccessor = $this->createValueAccessor();
		$this->id = $this->getValue($primaryKey);

		if ($datagrid->getColumnsSummary() instanceof ColumnsSummary) {
			$datagrid->getColumnsSummary()->add($this);
		}
	}

	public function getId(): mixed
	{
		if (is_object($this->id) && method_exists($this->id, '__toString')) {
			return (string) $this->id;
		}

		return $this->id;
	}

	public function getValue(mixed $key): mixed
	{
		return ($this->valueAccessor)($key);
	}

	public function getControl(): Html
	{
		return $this->control;
	}

	public function getControlClass(): string
	{
		$class = $this->control->getAttribute('class');

		if ($class === null) {
			return '';
		}

		return implode(' ', array_keys($class));
	}

	public function getActiveRowProperty(ActiveRow $item, string $key): mixed
	{
		if (preg_match('/^:([a-zA-Z0-9_$]+)\.([a-zA-Z0-9_$]+)(:([a-zA-Z0-9_$]+))?$/', $key, $matches) === 1) {
			$relatedTable = $matches[1];
			$relatedColumn = $matches[2];
			$throughColumn = $matches[4] ?? null;

			try {
				$relatedRow = $item->related($relatedTable, $throughColumn)->fetch();
			} catch (MemberAccessException) {
				return null;
			}

			return $relatedRow !== null
				? $relatedRow[$relatedColumn]
				: null;
		}

		if (preg_match('/^([a-zA-Z0-9_$]+)\.([a-zA-Z0-9_$]+)(:([a-zA-Z0-9_$]+))?$/', $key, $matches) === 1) {
			$referencedTable = $matches[1];
			$referencedColumn = $matches[2];
			$throughColumn = $matches[4] ?? null;

			try {
				$referencedRow = $item->ref($referencedTable, $throughColumn);
			} catch (MemberAccessException) {
				return null;
			}

			return $referencedRow === null
				? null
				: $referencedRow[$referencedColumn];
		}

		return $item[$key];
	}

	/**
	 * LeanMapper: Access object properties to get a item value
	 */
	public function getLeanMapperEntityProperty(Entity $item, string $key): mixed
	{
		$properties = explode('.', $key);
		$value = $item;

		for (;;) {
			$property = array_shift($properties);

			if ($property === null) {
				break;
			}

			if (!$value->__isset($property)) {
				if ($this->datagrid->strictEntityProperty) {
					throw new DatagridException(sprintf(
						'Target Property [%s] is not an object or is empty, trying to get [%s]',
						$value,
						str_replace('.', '->', $key)
					));
				}

				return null;
			}

			$value = $value->__get($property);
		}

		return $value;
	}

	/**
	 * Nextras: Access object properties to get a item value
	 */
	public function getNextrasEntityProperty(NextrasEntity $item, string $key): mixed
	{
		$properties = explode('.', $key);
		$value = $item;

		while ($property = array_shift($properties)) {
			if (!$value->__isset($property)) {
				if ($this->datagrid->strictEntityProperty) {
					throw new DatagridException(sprintf(
						'Target Property [%s] is not an object or is empty, trying to get [%s]',
						$value,
						str_replace('.', '->', $key)
					));
				}

				return null;
			}

			$value = $value->__get($property);
		}

		return $value;
	}

	/**
	 * Doctrine: Access object properties to get a item value
	 */
	public function getDoctrineEntityProperty(mixed $item, mixed $key): mixed
	{
		$properties = explode('.', $key);
		$value = $item;
		$accessor = PropertyAccessHelper::getAccessor();

		while ($property = array_shift($properties)) {
			if (!is_object($value) && ! (bool) $value) {
				if ($this->datagrid->strictEntityProperty) {
					throw new DatagridException(sprintf(
						'Target Property [%s] is not an object or is empty, trying to get [%s]',
						var_export($value, true),
						str_replace('.', '->', $key)
					));
				}

				return null;
			}

			$value = $accessor->getValue($value, $property);
		}

		if (is_object($value) && method_exists($value, '__toString')) {
			return (string) $value;
		}

		if (interface_exists(\BackedEnum::class) && $value instanceof \BackedEnum) {
			return $value->value;
		}

		return $value;
	}

	/**
	 * Get original item
	 */
	public function getItem(): mixed
	{
		return $this->item;
	}

	/**
	 * Has particular row group actions allowed?
	 */
	public function hasGroupAction(): bool
	{
		$condition = $this->datagrid->getRowCondition('group_action');

		if (is_callable($condition)) {
			return ($condition)($this->item);
		}

		return true;
	}

	/**
	 * Has particular row a action allowed?
	 */
	public function hasAction(mixed $key): bool
	{
		$condition = $this->datagrid->getRowCondition('action', $key);

		if (is_callable($condition)) {
			return ($condition)($this->item);
		}

		return true;
	}

	/**
	 * Has particular row inlie edit allowed?
	 */
	public function hasInlineEdit(): bool
	{
		$condition = $this->datagrid->getRowCondition('inline_edit');

		if (is_callable($condition)) {
			return ($condition)($this->item);
		}

		return true;
	}

	public function applyColumnCallback(string $key, Column $column): Column
	{
		$callback = $this->datagrid->getColumnCallback($key);

		if ($callback !== null) {
			call_user_func($callback, $column, $this->getItem());
		}

		return $column;
	}

	/**
	 * Create a type-dispatching closure based on the item type.
	 * This avoids repeated instanceof checks on every getValue() call.
	 */
	private function createValueAccessor(): \Closure
	{
		return match (true) {
			$this->item instanceof Entity => fn ($key) => $this->getLeanMapperEntityProperty($this->item, $key),
			$this->item instanceof NextrasEntity => fn ($key) => $this->getNextrasEntityProperty($this->item, $key),
			$this->item instanceof DibiRow => fn ($key) => $this->item[$this->formatDibiRowKey($key)],
			$this->item instanceof ActiveRow => fn ($key) => $this->getActiveRowProperty($this->item, $key),
			$this->item instanceof NetteRow => fn ($key) => $this->item->{$key},
			is_array($this->item) => fn ($key) => $this->getArrayValue($key),
			default => fn ($key) => $this->getDoctrineEntityProperty($this->item, $key),
		};
	}

	private function getArrayValue(mixed $key): mixed
	{
		$arrayValue = $this->item[$key];

		if (is_object($arrayValue) && method_exists($arrayValue, '__toString')) {
			return (string) $arrayValue;
		}

		if (interface_exists(\BackedEnum::class) && $arrayValue instanceof \BackedEnum) {
			return $arrayValue->value;
		}

		return $arrayValue;
	}

	/**
	 * Key may contain ".", get rid of it (+ the table alias)
	 */
	private function formatDibiRowKey(string $key): string
	{
		$offset = strpos($key, '.');

		if ($offset !== false) {
			return substr($key, $offset + 1);
		}

		return $key;
	}

}
