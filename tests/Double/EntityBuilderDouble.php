<?php
namespace Boxspaced\EntityManager\Test\Double;

use Boxspaced\EntityManager\Entity\EntityBuilder;

class EntityBuilderDouble extends EntityBuilder
{

    public function __construct()
    {

    }

    public function build($type, array $data)
    {
        $entity = new EntityDouble();
        $entity->setId($data['id']);
        $entity->setTitle($data['title']);
        $entity->setFname($data['fname']);
        $entity->setLname($data['lname']);
        return $entity;
    }

}
