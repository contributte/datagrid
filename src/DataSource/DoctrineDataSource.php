<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Jakub Kontra <me@jakubkontra.cz>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\DataSource;

use DibiFluent,
    Ublaboo\DataGrid\Filter\Filter,
    Nette\Utils\Callback,
    Nette\Utils\Strings;

class DoctrineDataSource implements IDataSource
{

    /**
     * @var \Doctrine\ORM\QueryBuilder
     */
    protected $data_source;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var string
     */
    protected $primary_key;


    public function __construct($data_source, $primary_key)
    {
        $this->data_source = $data_source;
        $this->primary_key = $primary_key;
    }

    /**
     * @return \Doctrine\ORM\Query
    */
    public function getQuery()
    {
        return $this->data_source->getQuery();
    }


    /********************************************************************************
     *                          IDataSource implementation                          *
     ********************************************************************************/


    /**
     * @return int
     */
    public function getCount()
    {
        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($this->getQuery());
        $count = count($paginator);

        return $count;
    }

    /**
     * @return array
     */
    public function getData()
    {
        // Paginator is better if the query uses ManyToMany associations
        $result = $this->data_source->getQuery()->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        $this->data = $result;
        return $this->data;
    }


    /**
     * @param array $filters
     * @return void
     */
    public function filter(array $filters)
    {
        foreach ($filters as $filter) {
            if ($filter->isValueSet()) {
                $or = [];

                if ($filter->hasConditionCallback()) {
                    Callback::invokeArgs(
                        $filter->getConditionCallback(),
                        [$this->data_source, $filter->getValue()]
                    );
                } else {
                    if ($filter instanceof Filter\FilterText) {
                        $this->applyFilterText($filter);
                    } else if ($filter instanceof Filter\FilterSelect) {
                        $this->applyFilterSelect($filter);
                    } else if ($filter instanceof Filter\FilterDate) {
                        $this->applyFilterDate($filter);
                    } else if ($filter instanceof Filter\FilterDateRange) {
                        $this->applyFilterDateRange($filter);
                    } else if ($filter instanceof Filter\FilterRange) {
                        $this->applyFilterRange($filter);
                    }
                }
            }
        }

        return $this;
    }


    /**
     * @param array $filter
     * @return void
     */
    public function filterOne(array $filter)
    {
        $this->data_source->where($filter);

        return $this;
    }


    public function applyFilterDateRange(Filter\FilterDateRange $filter)
    {
        return $this;
    }


    public function applyFilterRange(Filter\FilterRange $filter)
    {
        return $this;
    }


    public function applyFilterText(Filter\FilterText $filter)
    {
        return $this;
    }


    public function applyFilterSelect(Filter\FilterSelect $filter)
    {
        return $this;
    }


    public function applyFilterDate(Filter\FilterDate $filter)
    {
        return $this;
    }


    /**
     * @param int $offset
     * @param int $limit
     * @return void
     */
    public function limit($offset, $limit)
    {
        $this->data_source->setFirstResult($offset)->setMaxResults($limit);
        return $this;
    }


    public function sort(array $sorting)
    {
        return $this;
    }

}
