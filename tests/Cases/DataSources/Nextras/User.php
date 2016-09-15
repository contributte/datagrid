<?php

namespace Ublaboo\DataGrid\Tests\Cases\DataSources\Nextras;

use Nextras\Orm\Entity\Entity;
use Nextras\Orm\Relationships\OneHasMany;

/**
 * User
 *
 * @property int $id {primary}
 * @property string $name
 * @property int $age
 * @property string $address
 * @property City $city  {m:1 City::$users}
 */
class User extends Entity
{
}
