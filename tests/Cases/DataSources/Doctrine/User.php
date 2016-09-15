<?php

namespace Ublaboo\DataGrid\Tests\Cases\DataSources\Doctrine;

use \Doctrine\ORM\Mapping\Id;
use \Doctrine\ORM\Mapping\Entity;
use \Doctrine\ORM\Mapping\Table;
use \Doctrine\ORM\Mapping\Column;
use \Doctrine\ORM\Mapping\GeneratedValue;
use \Doctrine\ORM\Mapping\ManyToOne;
use \Doctrine\ORM\Mapping\JoinColumn;

/**
 * All properties are intentionally public so we can convert it to array in getActualResultAsArray
 * @Entity
 * @Table(name="users")
 **/
class User
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
     * @Column(type="integer")
     */
    public $age;

    /**
     * @Column(type="string")
     */
    public $address;

    /**
     * @ManyToOne(targetEntity="City")
     * @JoinColumn(name="city")
     */
    public $city;

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
    public function getAge()
    {
        return $this->age;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }
}
