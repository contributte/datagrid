<?php

namespace Ublaboo\DataGrid\Tests\Cases\DataSources\Doctrine;

use \Doctrine\ORM\Mapping\Id;
use \Doctrine\ORM\Mapping\Entity;
use \Doctrine\ORM\Mapping\Table;
use \Doctrine\ORM\Mapping\Column;
use \Doctrine\ORM\Mapping\GeneratedValue;
use \Doctrine\ORM\Mapping\OneToMany;

/**
 * All properties are intentionally public so we can convert it to array in getActualResultAsArray
 * @Entity
 * @Table(name="cities")
 **/
class City
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    public $id;

    /**
     * @Column(type="string")
     */
    public $name;


    /**
     * @Column(type="datetime")
     */
    public $created;

    /**
     * @OneToMany(targetEntity="User", mappedBy="city")
     */
    public $users;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @return mixed
     */
    public function getUsers()
    {
        return $this->users;
    }
}
