<?php

class EntityManager_UnitOfWork
{

    /**
     * @var EntityManager_EntityInterface[]
     */
    protected $_dirty = array();

    /**
     * @var EntityManager_EntityInterface[]
     */
    protected $_new = array();

    /**
     * @var EntityManager_EntityInterface[]
     */
    protected $_delete = array();

    /**
     * @var EntityManager_UnitOfWorkProcessorInterface
     */
    protected $_unitOfWorkProcessor;

    /**
     * @param EntityManager_UnitOfWorkProcessorInterface $unitOfWorkProcessor
     */
    public function __construct(
        EntityManager_UnitOfWorkProcessorInterface $unitOfWorkProcessor
    )
    {
        $this->_unitOfWorkProcessor = $unitOfWorkProcessor;
    }

    /**
     * @param EntityManager_EntityInterface $entity
     * @return EntityManager_UnitOfWork
     */
    public function persist(EntityManager_EntityInterface $entity)
    {
        if (!in_array($entity, $this->_new, true)) {
            $this->_new[] = $entity;
        }
        return $this;
    }

    /**
     * @param EntityManager_EntityInterface $entity
     * @return EntityManager_UnitOfWork
     */
    public function dirty(EntityManager_EntityInterface $entity)
    {
        if (!in_array($entity, $this->_new, true)) {
            $this->_dirty[$this->_globalKey($entity)] = $entity;
        }
        return $this;
    }

    /**
     * @param EntityManager_EntityInterface $entity
     * @return EntityManager_UnitOfWork
     */
    public function delete(EntityManager_EntityInterface $entity)
    {
        $this->_delete[$this->_globalKey($entity)] = $entity;
        return $this;
    }

    /**
     * @param EntityManager_EntityInterface $entity
     * @return EntityManager_UnitOfWork
     */
    public function clean(EntityManager_EntityInterface $entity)
    {
        unset($this->_delete[$this->_globalKey($entity)]);
        unset($this->_dirty[$this->_globalKey($entity)]);
        foreach ($this->_new as $key => $value) {
            if ($value === $entity) {
                unset($this->_new[$key]);
            }
        }
        return $this;
    }

    /**
     * @return EntityManager_UnitOfWork
     */
    public function flush()
    {
        $this->_unitOfWorkProcessor->process($this->_new, $this->_dirty, $this->_delete);
        $this->_new = array();
        $this->_dirty = array();
        $this->_delete = array();
        return $this;
    }

    /**
     * @param EntityManager_EntityInterface $entity
     * @return string
     */
    protected function _globalKey(EntityManager_EntityInterface $entity)
    {
        $key = get_class($entity) . '.' . $entity->getId();
        return $key;
    }

}
