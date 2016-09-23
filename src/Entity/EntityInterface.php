<?php
namespace EntityManager\Entity;

interface EntityInterface
{

    const TYPE_INT = 'int';
    const TYPE_FLOAT = 'float';
    const TYPE_STRING = 'string';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_DATETIME = 'datetime';

    /**
     * Array of 'camelCasedKey' to 'type' mapping
     *
     * e.g. 'firstName' => static::TYPE_STRING
     * e.g. 'id' => static::TYPE_INT
     *
     * @return array
     */
    public function getTypeMap();

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return EntityInterface
     */
    public function setId($id);

}
