<?php

namespace Ublaboo\DataGrid\Tests\Cases\DataSources\Nextras;

use Nextras\Dbal\Utils\DateTime;
use Nextras\Orm\Entity\Entity;
use Nextras\Orm\Relationships\OneHasMany;

/**
 * User
 *
 * @property int $id {primary}
 * @property string $name
 * @property DateTime $created
 * @property OneHasMany|User[]  $users  {1:m User::$city}
 */
class City extends Entity
{
}
