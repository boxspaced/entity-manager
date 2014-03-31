<?php

class EntityManager_UnitOfWork
{

    /**
     * @var EntityManager_EntityInterface[]
     */
    protected $dirty = array();

    /**
     * @var EntityManager_EntityInterface[]
     */
    protected $new = array();

    /**
     * @var EntityManager_EntityInterface[]
     */
    protected $delete = array();

    /**
     * @var EntityManager_UnitOfWorkProcessorInterface
     */
    protected $unitOfWorkProcessor;

    /**
     * @param EntityManager_UnitOfWorkProcessorInterface $unitOfWorkProcessor
     */
    public function __construct(
        EntityManager_UnitOfWorkProcessorInterface $unitOfWorkProcessor
    )
    {
        $this->unitOfWorkProcessor = $unitOfWorkProcessor;
    }

    /**
     * @param EntityManager_EntityInterface $entity
     * @return EntityManager_UnitOfWork
     */
    public function persist(EntityManager_EntityInterface $entity)
    {
        if (!in_array($entity, $this->new, true)) {
            $this->new[] = $entity;
        }
        return $this;
    }

    /**
     * @param EntityManager_EntityInterface $entity
     * @return EntityManager_UnitOfWork
     */
    public function dirty(EntityManager_EntityInterface $entity)
    {
        if (!in_array($entity, $this->new, true)) {
            $this->dirty[$this->_globalKey($entity)] = $entity;
        }
        return $this;
    }

    /**
     * @param EntityManager_EntityInterface $entity
     * @return EntityManager_UnitOfWork
     */
    public function delete(EntityManager_EntityInterface $entity)
    {
        $this->delete[$this->_globalKey($entity)] = $entity;
        return $this;
    }

    /**
     * @param EntityManager_EntityInterface $entity
     * @return EntityManager_UnitOfWork
     */
    public function clean(EntityManager_EntityInterface $entity)
    {
        unset($this->delete[$this->_globalKey($entity)]);
        unset($this->dirty[$this->_globalKey($entity)]);
        foreach ($this->new as $key => $value) {
            if ($value === $entity) {
                unset($this->new[$key]);
            }
        }
        return $this;
    }

    /**
     * @return EntityManager_UnitOfWork
     */
    public function flush()
    {
        $this->unitOfWorkProcessor->process($this->new, $this->dirty, $this->delete);
        $this->new = array();
        $this->dirty = array();
        $this->delete = array();
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
