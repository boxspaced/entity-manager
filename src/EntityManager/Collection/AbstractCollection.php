<?php

abstract class EntityManager_Collection_AbstractCollection implements
    Countable,
    IteratorAggregate
{

    /**
     * @var array
     */
    protected $_elements;

    /**
     * @var EntityManager_Builder_AbstractBuilder
     */
    protected $_builder;

    /**
     * @var Callable
     */
    protected $_rowsetCallback;

    /**
     * @return string
     */
    abstract protected function _getEntityClassName();

    /**
     * @param EntityManager_Builder_AbstractBuilder $builder
     * @param Callable $rowsetCallback
     */
    public function __construct(
        EntityManager_Builder_AbstractBuilder $builder,
        Callable $rowsetCallback = null
    )
    {
        $this->_builder = $builder;
        $this->_rowsetCallback = $rowsetCallback;
    }

    /**
     * @return array
     */
    protected function _getElements()
    {
        if ($this->_elements === null) {
            $this->_elements = array();
            $rowsetCallback = $this->_rowsetCallback;
            if (is_callable($rowsetCallback)) {
                $this->_elements = $rowsetCallback();
            }
        }
        return $this->_elements;
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        $this->_loadAllRows();
        return new ArrayIterator($this->_getElements());
    }

    /**
     *
     * @param EntityManager_EntityInterface $entity
     * @return EntityManager_Collection_AbstractCollection
     */
    public function add(EntityManager_EntityInterface $entity)
    {
        $this->_entityTypeCheck($entity);
        $this->_getElements();
        $this->_elements[] = $entity;
        return $this;
    }

    /**
     * @param int $key
     * @return EntityManager_Collection_AbstractCollection
     */
    public function remove($key)
    {
        $this->_getElements();
        unset($this->_elements[$key]);
        return $this;
    }

    /**
     * @return EntityManager_Collection_AbstractCollection
     */
    public function clear()
    {
        $this->_elements = array();
        return $this;
    }

    /**
     * @return EntityManager_EntityInterface|null
     */
    public function first()
    {
        $this->_getElements();
        reset($this->_elements);
        return $this->_getRow($this->key());
    }

    /**
     * @return EntityManager_EntityInterface|null
     */
    public function last()
    {
        $this->_getElements();
        end($this->_elements);
        return $this->_getRow($this->key());
    }

    /**
     * @return EntityManager_Collection_AbstractCollection
     */
    public function rewind()
    {
        $this->_getElements();
        reset($this->_elements);
        return $this;
    }

    /**
     * @return EntityManager_EntityInterface|null
     */
    public function current()
    {
        $this->_getElements();
        return $this->_getRow($this->key());
    }

    /**
     * @return int
     */
    public function key()
    {
        $this->_getElements();
        return key($this->_elements);
    }

    /**
     * @return EntityManager_EntityInterface|null
     */
    public function next()
    {
        $this->_getElements();
        next($this->_elements);
        return $this->_getRow($this->key());
    }

    /**
     * @return EntityManager_EntityInterface|null
     */
    public function prev()
    {
        $this->_getElements();
        prev($this->_elements);
        return $this->_getRow($this->key());
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return !is_null($this->current());
    }

    /**
     * @return int
     */
    public function count()
    {
        $this->_getElements();
        return count($this->_elements);
    }

    /**
     * @param Callable $callback
     * @return EntityManager_Collection_AbstractCollection
     */
    public function filter(Callable $callback)
    {
        $this->_loadAllRows();
        $filtered = array_filter($this->_elements, $callback);

        $return = $this->_builder->createCollection();
        foreach ($filtered as $entity) {
            $return->add($entity);
        }

        return $return;
    }

    /**
     * @return array
     */
    public function getKeys()
    {
        $this->_getElements();
        return array_keys($this->_elements);
    }

    /**
     * @param int $key
     * @return EntityManager_EntityInterface|null
     */
    protected function _getRow($key)
    {
        $this->_getElements();
        if (isset($this->_elements[$key])) {
            if (is_array($this->_elements[$key])) {
                $this->_elements[$key] = $this->_builder->build($this->_elements[$key]);
            }
            return $this->_elements[$key];
        }
        return null;
    }

    /**
     * @return EntityManager_Collection_AbstractCollection
     */
    protected function _loadAllRows()
    {
        $this->_getElements();
        foreach ($this->_elements as $key => $element) {
            $this->_getRow($key);
        }
        return $this;
    }

    /**
     * @param EntityManager_EntityInterface $entity
     * @return EntityManager_Collection_AbstractCollection
     * @throws EntityManager_Collection_Exception
     */
    protected function _entityTypeCheck(EntityManager_EntityInterface $entity)
    {
        if (!is_a($entity, $this->_getEntityClassName())) {
            throw new EntityManager_Collection_Exception('Entities passed to this collection must be of type: '
                    . $this->_getEntityClassName() . ' (' . get_class($entity) .') provided');
        }
        return $this;
    }

}
