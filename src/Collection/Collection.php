<?php
namespace Boxspaced\EntityManager\Collection;

use Boxspaced\EntityManager\Entity\EntityBuilder;
use Boxspaced\EntityManager\UnitOfWork;
use Boxspaced\EntityManager\Entity\AbstractEntity;
use Boxspaced\EntityManager\Exception;
use Countable;
use IteratorAggregate;
use ArrayIterator;

class Collection implements
    Countable,
    IteratorAggregate
{

    /**
     * @var array
     */
    protected $elements;

    /**
     * @var EntityBuilder
     */
    protected $entityBuilder;

    /**
     * @var UnitOfWork
     */
    protected $unitOfWork;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var callable
     */
    protected $rowset;

    /**
     * @param UnitOfWork $unitOfWork
     * @param EntityBuilder $entityBuilder
     * @param string $type
     */
    public function __construct(
        UnitOfWork $unitOfWork,
        EntityBuilder $entityBuilder,
        $type
    )
    {
        $this->unitOfWork = $unitOfWork;
        $this->entityBuilder = $entityBuilder;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param callable $rowset
     * @return Collection
     */
    public function setRowset(callable $rowset)
    {
        $this->rowset = $rowset;
        return $this;
    }

    /**
     * @return array
     */
    protected function getElements()
    {
        if ($this->elements === null) {

            $this->elements = [];

            if (null !== $this->rowset) {
                $this->elements = call_user_func($this->rowset);
            }
        }

        return $this->elements;
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        $this->loadAllRows();
        return new ArrayIterator($this->getElements());
    }

    /**
     *
     * @param AbstractEntity $entity
     * @return Collection
     */
    public function add(AbstractEntity $entity)
    {
        $this->entityTypeCheck($entity);
        $this->getElements();
        $this->elements[] = $entity;
        return $this;
    }

    /**
     * @param AbstractEntity $entity
     * @return Collection
     */
    public function delete(AbstractEntity $entity)
    {
        foreach ($this as $key => $value) {

            if ($value !== $entity) {
                continue;
            }

            if ($entity->get('id')) {
                $this->unitOfWork->delete($entity);
            } else {
                $this->unitOfWork->clean($entity);
            }

            $this->remove($key);
        }

        return $this;
    }

    /**
     * @param int $key
     * @return Collection
     */
    public function remove($key)
    {
        $this->getElements();
        unset($this->elements[$key]);
        return $this;
    }

    /**
     * @return Collection
     */
    public function clear()
    {
        $this->elements = [];
        return $this;
    }

    /**
     * @return AbstractEntity
     */
    public function first()
    {
        $this->getElements();
        reset($this->elements);
        return $this->getRow($this->key());
    }

    /**
     * @return AbstractEntity
     */
    public function last()
    {
        $this->getElements();
        end($this->elements);
        return $this->getRow($this->key());
    }

    /**
     * @return Collection
     */
    public function rewind()
    {
        $this->getElements();
        reset($this->elements);
        return $this;
    }

    /**
     * @return AbstractEntity
     */
    public function current()
    {
        $this->getElements();
        return $this->getRow($this->key());
    }

    /**
     * @return int
     */
    public function key()
    {
        $this->getElements();
        return key($this->elements);
    }

    /**
     * @return AbstractEntity
     */
    public function next()
    {
        $this->getElements();
        next($this->elements);
        return $this->getRow($this->key());
    }

    /**
     * @return AbstractEntity
     */
    public function prev()
    {
        $this->getElements();
        prev($this->elements);
        return $this->getRow($this->key());
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
        $this->getElements();
        return count($this->elements);
    }

    /**
     * @param callable $callback
     * @return Collection
     */
    public function filter(callable $callback)
    {
        $this->loadAllRows();
        $filtered = array_filter($this->elements, $callback);

        $collection = new static(
            $this->unitOfWork,
            $this->entityBuilder,
            $this->type
        );

        foreach ($filtered as $entity) {
            $collection->add($entity);
        }

        return $collection;
    }

    /**
     * @param callable $callback
     * @return Collection
     */
    public function sort(callable $callback)
    {
        $this->loadAllRows();
        usort($this->elements, $callback);
        return $this;
    }

    /**
     * @return array
     */
    public function getKeys()
    {
        $this->getElements();
        return array_keys($this->elements);
    }

    /**
     * @param int $key
     * @return AbstractEntity
     */
    protected function getRow($key)
    {
        $this->getElements();

        if (!isset($this->elements[$key])) {
            return null;
        }

        if (is_array($this->elements[$key])) {
            $this->elements[$key] = $this->entityBuilder->build($this->type, $this->elements[$key]);
        }

        return $this->elements[$key];
    }

    /**
     * @return Collection
     */
    protected function loadAllRows()
    {
        $this->getElements();

        foreach ($this->elements as $key => $element) {
            $this->getRow($key);
        }

        return $this;
    }

    /**
     * @param AbstractEntity $entity
     * @return Collection
     * @throws Exception\InvalidArgumentException
     */
    protected function entityTypeCheck(AbstractEntity $entity)
    {
        if (!($entity instanceof $this->type)) {

            throw new Exception\InvalidArgumentException(sprintf(
                'Entities passed to this collection must be of type: %s provided: %s',
                $this->type,
                get_class($entity)
            ));
        }

        return $this;
    }

}
