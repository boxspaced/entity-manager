<?php
namespace EntityManager\Test\Double;

use EntityManager\Entity\EntityInterface;
use EntityManager\Mapper\Conditions\Conditions;

class MapperStrategy implements \EntityManager\Mapper\StrategyInterface
{

    public $data = [];

    public function find($type, $id)
    {
        return array_shift($this->data);
    }

    public function findOne($type, Conditions $conditions = null)
    {
        return array_shift($this->data);
    }

    public function findAll($type, Conditions $conditions = null)
    {
        return $this->data;
    }

    public function insert(EntityInterface $entity)
    {
        $id = max(array_column($this->data, 'id')) + 1;
        $entity->setId($id);
        return $this;
    }

    public function update(EntityInterface $entity)
    {
        return $this;
    }

    public function delete(EntityInterface $entity)
    {
        return $this;
    }

}
