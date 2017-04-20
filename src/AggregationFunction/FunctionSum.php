<?php

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

final class FunctionSum implements IAggregationFunction
{
	/**
	 * @var string
	 */
	private $column;

	/**
	 * @var int
	 */
	private $result = 0;

	/**
	 * @var int
	 */
	private $dataType;

	/**
	 * @var callable
	 */
	private $renderer;


	/**
	 * @param string $column
	 * @param int $dataType
	 */
	public function __construct($column, $dataType = IAggregationFunction::DATA_TYPE_PAGINATED)
	{
		$this->column = $column;
		$this->dataType = $dataType;
	}


	/**
	 * @return bool
	 */
	public function getFilterDataType()
	{
		return $this->dataType;
	}


	/**
	 * @param  mixed  $dataSource
	 * @return void
	 */
	public function processDataSource($dataSource)
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
				: current($dataSource->getRootAliases()).'.'.$this->column;

			$this->result = $dataSource
				->select(sprintf('SUM(%s)', $column))
				->getQuery()
				->getSingleScalarResult();
		}

		if ($dataSource instanceof Collection) {
			$this->result = 0;	// Must be cleared as this method could be called multiple times

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
	public function setRenderer(callable $callback = NULL)
	{
		$this->renderer = $callback;
		return $this;
	}
}
