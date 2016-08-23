<?php

namespace Ublaboo\DataGrid\Tests\Files\Orm;

use Nextras\Orm\Relationships\OneHasMany;

/**
 * User
 * 
 * @property int $id {primary}
 * @property string $username
 * @property string $name
 * @property int $age
 * @property Role $role {m:1 Role::$users}
 */
class User extends \Nextras\Orm\Entity\Entity {
    
}

class UsersMapper extends \Nextras\Orm\Mapper\Mapper {
    
}

class UsersRepository extends \Nextras\Orm\Repository\Repository {

    public static function getEntityClassNames() {
        return [User::class];
    }

}

/**
 * Role
 * 
 * @property int $id {primary}
 * @property \Nextras\Dbal\Utils\DateTime $inserted
 * @property string $name
 * @property OneHasMany|User[] $users {1:m User::$role}
 */
class Role extends \Nextras\Orm\Entity\Entity {
    
}

class RolesMapper extends \Nextras\Orm\Mapper\Mapper {
    
}

class RolesRepository extends \Nextras\Orm\Repository\Repository {

    public static function getEntityClassNames() {
        return [Role::class];
    }

}
