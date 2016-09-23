<?php
namespace EntityManager\Entity;

use EntityManager\UnitOfWork;

abstract class AbstractEntity
{

    const TYPE_INT = 'int';
    const TYPE_FLOAT = 'float';
    const TYPE_STRING = 'string';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_DATETIME = 'datetime';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var UnitOfWork
     */
    protected $unitOfWork;

    /**
     * Array of 'camelCasedKey' to 'type' mapping
     *
     * e.g. 'firstName' => static::TYPE_STRING
     * e.g. 'id' => static::TYPE_INT
     *
     * @return array
     */
    abstract public function getTypeMap();

    /**
     * @param UnitOfWork $unitOfWork
     */
    public function __construct(UnitOfWork $unitOfWork)
    {
        $this->unitOfWork = $unitOfWork;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return AbstractEntity
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return AbstractEntity
     */
    protected function markDirty()
    {
        if ($this->id) {
            $this->unitOfWork->dirty($this);
        }

        return $this;
    }

}
