<?php

namespace Ublaboo\DataGrid\Tests\Cases\DataSources\Nextras;

use Nextras\Orm\Repository\Repository;

/**
 * Class CitiesRepository
 * @package Ublaboo\DataGrid\Tests\Cases\DataSources\Nextras
 */
class CitiesRepository extends Repository
{
    /**
     * @return array
     */
    public static function getEntityClassNames()
    {
        return [City::class];
    }
}
