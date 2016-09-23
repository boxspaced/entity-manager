<?php
namespace EntityManager\Test\Double;

use EntityManager\Entity\EntityInterface;
use EntityManager\Mapper\Conditions\Conditions;

class Mapper extends \EntityManager\Mapper\Mapper
{

    public $entities = [];

    public $inserted = [];

    public $updated = [];

    public $deleted = [];

    public function __construct()
    {

    }

    public function find($type, $id)
    {
        return array_shift($this->entities);
    }

    public function findOne($type, Conditions $conditions = null)
    {
        return array_shift($this->entities);
    }

    public function findAll($type, Conditions $conditions = null)
    {
        return $this->entities;
    }

    public function insert(EntityInterface $entity)
    {
        $this->inserted[] = $entity;
        return $this;
    }

    public function update(EntityInterface $entity)
    {
        $this->updated[] = $entity;
        return $this;
    }

    public function delete(EntityInterface $entity)
    {
        $this->deleted[] = $entity;
        return $this;
    }

}
