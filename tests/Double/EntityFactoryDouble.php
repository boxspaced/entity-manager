<?php
namespace Boxspaced\EntityManager\Test\Double;

use Boxspaced\EntityManager\Entity\EntityFactory;

class EntityFactoryDouble extends EntityFactory
{

    public function __construct()
    {

    }

    public function create($type)
    {
        return new EntityDouble();
    }

}
