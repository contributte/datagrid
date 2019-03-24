<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\AggregationFunction;

interface IAggregationFunction
{

	public const DATA_TYPE_ALL = 'data_type_all';
	public const DATA_TYPE_FILTERED = 'data_type_filtered';
	public const DATA_TYPE_PAGINATED = 'data_type_paginated';

	public function getFilterDataType(): string;


	public function processDataSource($dataSource): void;


	public function renderResult(): void;

}
