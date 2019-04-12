<?php
declare(strict_types=1);

namespace Ublaboo\DataGrid\Tests\Files;

use Nette\SmartObject;

class XTestingDDataGridEntity
{

	use SmartObject;

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 * @var integer
	 */
	private $id;

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $name;

	/**
	 * @ORM\Column(type="integer")
	 * @var integer
	 */
	private $age;

	private $partner;


	public function __construct($args)
	{
		$this->id = $args['id'];
		$this->age = $args['age'];
		$this->name = $args['name'];
	}


	public function getName()
	{
		return $this->name;
	}


	/**
	 * @return integer
	 */
	final public function getId()
	{
		return $this->id;
	}


	public function getAge()
	{
		return $this->age;
	}


	public function setPartner($p)
	{
		$this->partner = $p;
	}


	public function getPartner()
	{
		return $this->partner;
	}

}
