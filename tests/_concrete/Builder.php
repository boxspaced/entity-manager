<?php

require_once 'Collection.php';
require_once 'Entity.php';

class Builder extends EntityManager_Builder_AbstractBuilder
{

    public function createCollection(Closure $rowsetCallback = null)
    {
        return new Collection($this, $rowsetCallback);
    }

    protected function buildEntity(array $row)
    {
        $entity = new Entity($this->entityManager);
        $entity->setId($row['id']);
        $entity->setTitle($row['title']);
        $entity->setFname($row['fname']);
        $entity->setLname($row['lname']);
        return $entity;
    }

    protected function getEntityClassName()
    {
        return 'Entity';
    }

}
