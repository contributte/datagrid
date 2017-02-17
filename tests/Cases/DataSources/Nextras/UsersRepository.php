<?php

namespace Ublaboo\DataGrid\Tests\Cases\DataSources\Nextras;

use Nextras\Orm\Repository\Repository;

/**
 * Class UsersRepository
 * @package Ublaboo\DataGrid\Tests\Cases\DataSources\Nextras
 */
class UsersRepository extends Repository
{
    /**
     * @return array
     */
    public static function getEntityClassNames()
    {
        return [User::class];
    }
}
