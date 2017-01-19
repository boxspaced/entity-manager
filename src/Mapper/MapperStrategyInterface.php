<?php
namespace Boxspaced\EntityManager\Mapper;

use Boxspaced\EntityManager\Entity\AbstractEntity;

interface MapperStrategyInterface
{

    /**
     * @param string $type
     * @param int $id
     * @return array
     */
    public function find($type, $id);

    /**
     * @param string $type
     * @param Query $query
     * @return array
     */
    public function findOne($type, Query $query = null);

    /**
     * @param string $type
     * @param Query $query
     * @return array
     */
    public function findAll($type, Query $query = null);

    /**
     * @param AbstractEntity $entity
     * @return void
     */
    public function insert(AbstractEntity $entity);

    /**
     * @param AbstractEntity $entity
     * @return void
     */
    public function update(AbstractEntity $entity);

    /**
     * @param AbstractEntity $entity
     * @return void
     */
    public function delete(AbstractEntity $entity);

}
