<?php
namespace Boxspaced\EntityManager\Mapper;

use Boxspaced\EntityManager\Entity\AbstractEntity;
use Boxspaced\EntityManager\Mapper\Conditions\Conditions;

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
