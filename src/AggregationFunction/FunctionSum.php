<?php declare(strict_types = 1);

namespace Contributte\Datagrid\AggregationFunction;

use Contributte\Datagrid\Utils\PropertyAccessHelper;
use Dibi\Fluent;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use Nette\Database\Table\Selection;
use Nextras\Orm\Collection\DbalCollection;
use Nextras\Orm\Collection\ICollection;

class FunctionSum implements ISingleColumnAggregationFunction
{

	protected int $result = 0;

	/** @var callable|null */
	protected $renderer = null;

	public function __construct(protected string $column, protected string $dataType = IAggregationFunction::DATA_TYPE_PAGINATED)
	{
	}

	public function getFilterDataType(): string
	{
		return $this->dataType;
	}

	public function processDataSource(Fluent|QueryBuilder|Collection|Selection|ICollection $dataSource): void
	{
		if ($dataSource instanceof Fluent) {
			$connection = $dataSource->getConnection();
			$this->result = (int) $connection->select('SUM(%n)', $this->column)
				->from($dataSource, 's')
				->fetchSingle();
		}

		if ($dataSource instanceof QueryBuilder) {
			$column = str_contains($this->column, '.')
				? $this->column
				: current($dataSource->getRootAliases()) . '.' . $this->column;

			$this->result = (int) $dataSource
				->select(sprintf('SUM(%s)', $column))
				->setMaxResults(1)
				->setFirstResult(0)
				->getQuery()
				->getSingleScalarResult();
		}

		if ($dataSource instanceof Collection) {
			$dataSource->forAll(function ($key, $value): bool {
				$this->result += PropertyAccessHelper::getValue($value, $this->column);

				return true;
			});
		}

		if ($dataSource instanceof DbalCollection) {
			foreach ($dataSource->fetchAll() as $item)
				$this->result += $item->getValue($this->column);
		}
	}

	public function renderResult(): mixed
	{
		$result = $this->result;

		if (isset($this->renderer)) {
			$result = call_user_func($this->renderer, $result);
		}

		return $result;
	}

	/**
	 * @return static
	 */
	public function setRenderer(?callable $callback = null): self
	{
		$this->renderer = $callback;

		return $this;
	}

}
