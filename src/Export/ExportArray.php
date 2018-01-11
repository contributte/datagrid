<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Export;

use Nette\Utils\Callback;
use Ublaboo\DataGrid\CsvDataModel;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Row;

class ExportArray extends Export
{
    /**
     * @var bool
     */
    protected $is_ajax = false;

    /**
     * @var DataGrid
     */
    protected $grid;

    /**
     * @var bool
     */
    protected $include_header = false;

    /**
     * @param string $text
     * @param DataGrid $grid
     * @param callable $callback
     * @param boolean $include_header
     * @param boolean $filtered
     */
    public function __construct($grid, $text, $callback, $include_header, $filtered)
    {
        $this->grid = $grid;
        $this->callback = $callback;
        $this->filtered = (bool) $filtered;
        $this->title = $text;
        $this->text = $text;
    }


    /**
     * Call export callback
     * @param  array    $data
     * @return void
     */
    public function invoke(array $data)
    {
        $columns = $this->getColumns() ?: $this->grid->getColumns();
        $rows = [];
        foreach ($data as $item) {
            $rows[] = $this->getRow($item);
        }

        // Prepare CSV data model
        $csv_data_model = new CsvDataModel($rows, $columns, $this->grid->getTranslator());

        $arrayData = $csv_data_model->getSimpleData($this->include_header);

        Callback::invokeArgs($this->callback, [$arrayData, $this->grid]);
    }


    /**
     * @param $item
     * @return Row
     */
    private function getRow($item)
    {
        return new Row($this->grid, $item, $this->grid->getPrimaryKey());
    }
}
