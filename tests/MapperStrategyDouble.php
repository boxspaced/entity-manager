<?php
namespace Boxspaced\EntityManager\Test;

use Boxspaced\EntityManager\Entity\AbstractEntity;
use Boxspaced\EntityManager\Mapper\Conditions;
use Boxspaced\EntityManager\Mapper\MapperStrategyInterface;

class MapperStrategyDouble implements MapperStrategyInterface
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

    public function insert(AbstractEntity $entity)
    {
        $id = max(array_column($this->data, 'id')) + 1;
        $entity->setId($id);
        return $this;
    }

    public function update(AbstractEntity $entity)
    {
        return $this;
    }

    public function delete(AbstractEntity $entity)
    {
        return $this;
    }

}
