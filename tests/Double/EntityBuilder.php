<?php
namespace EntityManager\Test\Double;

class EntityBuilder extends \EntityManager\Entity\Builder
{

    public function __construct()
    {

    }

    public function build($type, array $data)
    {
        $entity = new Entity();
        $entity->setId($data['id']);
        $entity->setTitle($data['title']);
        $entity->setFname($data['fname']);
        $entity->setLname($data['lname']);
        return $entity;
    }

}
