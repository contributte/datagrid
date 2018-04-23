<?php declare(strict_types = 1);

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\AggregationFunction;

use DibiFluent;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use Nette\Utils\Strings;
use Ublaboo\DataGrid\Utils\PropertyAccessHelper;

class FunctionSum implements IAggregationFunction
{

	/**
	 * @var string
	 */
	protected $column;

	/**
	 * @var int
	 */
	protected $result = 0;

	/**
	 * @var int
	 */
	protected $dataType;

	/**
	 * @var callable
	 */
	protected $renderer;

	public function __construct(string $column, string $dataType = IAggregationFunction::DATA_TYPE_PAGINATED)
	{
		$this->column = $column;
		$this->dataType = $dataType;
	}


	public function getFilterDataType(): string
	{
		return $this->dataType;
	}


	/**
	 * @param  mixed  $dataSource
	 */
	public function processDataSource($dataSource): void
	{
		if ($dataSource instanceof DibiFluent) {
			$connection = $dataSource->getConnection();
			$this->result = $connection->select('SUM(%n) AS sum', $this->column)
				->from($dataSource, 's')
				->fetch()
				->sum;
		}

		if ($dataSource instanceof QueryBuilder) {
			$column = Strings::contains($this->column, '.')
				? $this->column
				: current($dataSource->getRootAliases()) . '.' . $this->column;

			$this->result = $dataSource
				->select(sprintf('SUM(%s)', $column))
				->getQuery()
				->getSingleScalarResult();
		}

		if ($dataSource instanceof Collection) {
			$dataSource->forAll(function ($key, $value) {
				$this->result += PropertyAccessHelper::getValue($value, $this->column);
			});
		}
	}


	/**
	 * @return mixed
	 */
	public function renderResult()
	{
		$result = $this->result;

		if (isset($this->renderer)) {
			$result = call_user_func($this->renderer, $result);
		}

		return $result;
	}


	/**
	 * @param  callable|NULL  $callback
	 * @return static
	 */
	public function setRenderer(?callable $callback = null)
	{
		$this->renderer = $callback;
		return $this;
	}

}
