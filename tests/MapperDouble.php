<?php
namespace Boxspaced\EntityManager\Test;

use Boxspaced\EntityManager\Entity\AbstractEntity;
use Boxspaced\EntityManager\Mapper\Query;
use Boxspaced\EntityManager\Mapper\Mapper;

class MapperDouble extends Mapper
{

    public $id;

    public $type;

    public $query;

    public $entities = [];

    public $inserted = [];

    public $updated = [];

    public $deleted = [];

    public function __construct()
    {

    }

    public function find($type, $id)
    {
        $this->id = $id;
        $this->type = $type;
        return array_shift($this->entities);
    }

    public function findOne($type, Query $query = null)
    {
        $this->type = $type;
        $this->query = $query;
        return array_shift($this->entities);
    }

    public function findAll($type, Query $query = null)
    {
        $this->type = $type;
        $this->query = $query;

        $collection = new CollectionDouble(
            new UnitOfWorkDouble(),
            new EntityBuilderDouble(),
            $type
        );

        $collection->setElements($this->entities);

        return $collection;
    }

    public function insert(AbstractEntity $entity)
    {
        $this->inserted[] = $entity;
        return $this;
    }

    public function update(AbstractEntity $entity)
    {
        $this->updated[] = $entity;
        return $this;
    }

    public function delete(AbstractEntity $entity)
    {
        $this->deleted[] = $entity;
        return $this;
    }

}
