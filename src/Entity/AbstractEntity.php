<?php
namespace EntityManager\Entity;

use Zend\Config\Config;
use EntityManager\Collection\Factory as CollectionFactory;
use EntityManager\Collection\Collection;
use EntityManager\UnitOfWork;
use InvalidArgumentException;

abstract class AbstractEntity
{

    const TYPE_INT = 'int';
    const TYPE_FLOAT = 'float';
    const TYPE_STRING = 'string';
    const TYPE_BOOL = 'bool';
    const TYPE_DATETIME = 'datetime';

    /**
     * @var UnitOfWork
     */
    protected $unitOfWork;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var bool
     */
    protected $strict = true;

    /**
     * @param UnitOfWork $unitOfWork
     * @param CollectionFactory $collectionFactory
     * @param Config $config
     * @throws InvalidArgumentException
     */
    public function __construct(
        UnitOfWork $unitOfWork,
        CollectionFactory $collectionFactory,
        Config $config
    )
    {
        $this->unitOfWork = $unitOfWork;
        $this->collectionFactory = $collectionFactory;

        $this->strict = !empty($config->strict);

        $type = get_class($this);

        if (!isset($config->types->{$type}->entity)) {
            throw new InvalidArgumentException("Entity config missing for type: {$type}");
        }

        $this->config = $config->types->{$type}->entity;

        $this->initChildren();
    }

    /**
     * @return AbstractEntity
     * @throws InvalidArgumentException
     */
    protected function initChildren()
    {
        foreach ($this->config->get('children', []) as $field => $childrenConfig) {

            if (!isset($childrenConfig->type)) {
                throw new InvalidArgumentException("Type config missing for field: {$field}");
            }

            $collection = $this->collectionFactory->create($childrenConfig->type);

            $this->set($field, $collection);
        }

        return $this;
    }

    /**
     * @param string $field
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function get($field)
    {
        if (!$this->has($field)) {
            throw new InvalidArgumentException(
                "Entity does not have field defined: {$field}"
            );
        }

        if (isset($this->fields[$field])) {

            if (is_callable($this->fields[$field])) {
                return call_user_func($this->fields[$field]);
            }

            return $this->fields[$field];
        }

        return null;
    }

    /**
     * @param string $field
     * @return bool
     */
    public function has($field)
    {
        return (
            isset($this->config->fields->{$field})
            || isset($this->config->children->{$field})
        );
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return AbstractEntity
     * @throws InvalidArgumentException
     */
    public function set($field, $value)
    {
        if (isset($this->config->children->{$field})) {

            $this->setChildren($field, $value);
            return $this;
        }

        $type = $this->getFieldType($field);

        switch ($type) {

            case static::TYPE_STRING:
                $valid = is_string($value);
                break;

            case static::TYPE_INT:
                $valid = is_int($value);
                break;

            case static::TYPE_FLOAT:
                $valid = is_float($value);
                break;

            case static::TYPE_BOOL:
                $valid = is_bool($value);
                break;

            case static::TYPE_DATETIME:
                $valid = ($value instanceof \DateTime);
                break;

            default:
                $valid = (
                    !is_callable($value)
                    && !($value instanceof $type)
                );
        }

        if (null !== $value && $this->strict && !$valid) {
            throw new InvalidArgumentException("Invalid value for field: {$field}");
        }

        $this->fields[$field] = $value;

        if (null !== $this->get('id')) {
            $this->unitOfWork->dirty($this);
        }

        return $this;
    }

    /**
     * @param string $field
     * @param Collection $collection
     * @return AbstractEntity
     * @throws InvalidArgumentException
     */
    protected function setChildren($field, Collection $collection)
    {
        $type = $this->getChildType($field);

        if ($collection->getType() !== $type) {

            throw new InvalidArgumentException(sprintf(
                'The collection must be of type: %s but provided: %s for field: %s',
                $type,
                $collection->getType(),
                $field
            ));
        }

        $this->fields[$field] = $collection;

        return $this;
    }

    /**
     * @param string $field
     * @return string
     * @throws InvalidArgumentException
     */
    protected function getFieldType($field)
    {
        if (!isset($this->config->fields->{$field}->type)) {
            throw new InvalidArgumentException(
                "Field type has not been defined for field: {$field}"
            );
        }

        return $this->config->fields->{$field}->type;
    }

    /**
     * @param string $field
     * @return string
     * @throws InvalidArgumentException
     */
    protected function getChildType($field)
    {
        if (!isset($this->config->children->{$field}->type)) {
            throw new InvalidArgumentException(
                "Child type has not been defined for field: {$field}"
            );
        }

        return $this->config->children->{$field}->type;
    }

}
