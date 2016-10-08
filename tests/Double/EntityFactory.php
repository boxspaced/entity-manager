<?php
namespace Boxspaced\EntityManager\Test\Double;

class EntityFactory extends \Boxspaced\EntityManager\Entity\Factory
{

    public function __construct()
    {

    }

    public function create($type)
    {
        return new Entity();
    }

}
