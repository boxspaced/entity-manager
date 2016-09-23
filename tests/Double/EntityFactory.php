<?php
namespace EntityManager\Test\Double;

class EntityFactory extends \EntityManager\Entity\Factory
{

    public function __construct()
    {

    }

    public function create($type)
    {
        return new Entity();
    }

}
