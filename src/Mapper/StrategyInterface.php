<?php
namespace EntityManager\Mapper;

use EntityManager\Entity\EntityInterface;
use EntityManager\Mapper\Conditions\Conditions;

interface StrategyInterface
{

    /**
     * @param string $type
     * @param int $id
     * @return array
     */
    public function find($type, $id);

    /**
     * @param string $type
     * @param Conditions $conditions
     * @return array
     */
    public function findOne($type, Conditions $conditions = null);

    /**
     * @param string $type
     * @param Conditions $conditions
     * @return array
     */
    public function findAll($type, Conditions $conditions = null);

    /**
     * @param EntityInterface $entity
     * @return void
     */
    public function insert(EntityInterface $entity);

    /**
     * @param EntityInterface $entity
     * @return void
     */
    public function update(EntityInterface $entity);

    /**
     * @param EntityInterface $entity
     * @return void
     */
    public function delete(EntityInterface $entity);

}
