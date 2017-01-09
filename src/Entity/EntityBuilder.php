<?php
namespace Boxspaced\EntityManager\Entity;

use Boxspaced\EntityManager\IdentityMap;
use Boxspaced\EntityManager\UnitOfWork;
use Boxspaced\EntityManager\Mapper\MapperFactory;
use Boxspaced\EntityManager\Collection\Collection;
use Boxspaced\EntityManager\Mapper\Conditions;
use Boxspaced\EntityManager\Exception;
use DateTime;

class EntityBuilder
{

    /**
     * @var IdentityMap
     */
    protected $identityMap;

    /**
     * @var UnitOfWork
     */
    protected $unitOfWork;

    /**
     * @var EntityFactory
     */
    protected $entityFactory;

    /**
     * @var MapperFactory
     */
    protected $mapperFactory;

    /**
     * @var array
     */
    protected $config;

    /**
     * @param IdentityMap $identityMap
     * @param UnitOfWork $unitOfWork
     * @param EntityFactory $entityFactory
     * @param MapperFactory $mapperFactory
     * @param array $config
     */
    public function __construct(
        IdentityMap $identityMap,
        UnitOfWork $unitOfWork,
        EntityFactory $entityFactory,
        MapperFactory $mapperFactory,
        array $config
    )
    {
        $this->identityMap = $identityMap;
        $this->unitOfWork = $unitOfWork;
        $this->entityFactory = $entityFactory;
        $this->mapperFactory = $mapperFactory;
        $this->config = $config;
    }

    /**
     * @param string
     * @param array $data
     * @return AbstractEntity
     * @throws Exception\UnexpectedValueException
     */
    public function build($type, array $data)
    {
        if (!$data) {
            throw new Exception\UnexpectedValueException(
                'Data array empty, use factories to create new entities'
            );
        }

        if (empty($data['id'])) {
            throw new Exception\UnexpectedValueException(
                "No 'id' field in data, use factories to create new entities"
            );
        }

        $existing = $this->identityMap->exists($type, $data['id']);

        if ($existing) {
            return $existing;
        }

        $entity = $this->createEntity($type, $data);

        $this->unitOfWork->clean($entity); // Data loading will have marked entity dirty
        $this->identityMap->add($entity);

        return $entity;
    }

    /**
     * @param string $type
     * @param array $data
     * @return AbstractEntity
     */
    protected function createEntity($type, array $data)
    {
        $entity = $this->entityFactory->create($type);

        $this->setEntityFields($entity, $data);
        $this->setEntityOneToMany($entity);

        return $entity;
    }

    /**
     * @param AbstractEntity $entity
     * @param array $data
     * @return Builder
     */
    protected function setEntityFields(AbstractEntity $entity, array $data)
    {
        $entityConfig = $this->getEntityConfig(get_class($entity));

        foreach (isset($entityConfig['fields']) ? $entityConfig['fields'] : [] as $field => $fieldConfig) {

            if (!isset($fieldConfig['type'])) {
                throw new Exception\InvalidArgumentException("Type config missing for field: {$field}");
            }

            if (!isset($data[$field])) {
                continue;
            }

            switch ($fieldConfig['type']) {

                case $entity::TYPE_STRING:
                    $entity->set($field, strval($data[$field]));
                    break;

                case $entity::TYPE_INT:
                    $entity->set($field, intval($data[$field]));
                    break;

                case $entity::TYPE_FLOAT:
                    $entity->set($field, floatval($data[$field]));
                    break;

                case $entity::TYPE_BOOL:
                    $entity->set($field, boolval($data[$field]));
                    break;

                case $entity::TYPE_DATETIME:
                    $entity->set($field, new DateTime($data[$field]));
                    break;

                default:
                    $oneToOne = $this->getOneToOne($fieldConfig['type'], $data[$field]);
                    $entity->set($field, $oneToOne);
            }
        }

        return $this;
    }

    /**
     * @param string $type
     * @return array
     * @throws Exception\InvalidArgumentException
     */
    protected function getEntityConfig($type)
    {
        if (!isset($this->config['types'][$type]['entity'])) {
            throw new Exception\InvalidArgumentException("Entity config missing for type: {$type}");
        }

        return $this->config['types'][$type]['entity'];
    }

    /**
     * @param string $type
     * @param int $id
     * @return callable
     */
    protected function getOneToOne($type, $id)
    {
        if (!$id) {
            return null;
        }

        $callback = function() use ($type, $id) {
            return $this->mapperFactory->createForType($type)->find($type, $id);
        };
        $callback->bindTo($this);

        return $callback;
    }

    /**
     * @param AbstractEntity $entity
     * @return Builder
     * @throws Exception\UnexpectedValueException
     */
    protected function setEntityOneToMany(AbstractEntity $entity)
    {
        $entityConfig = $this->getEntityConfig(get_class($entity));

        foreach (isset($entityConfig['oneToMany']) ? $entityConfig['oneToMany'] : [] as $field => $oneToManyConfig) {

            if (!is_callable($oneToManyConfig['conditions'])) {
                throw new Exception\UnexpectedValueException("The 'one to many' conditions must be callable");
            }

            $conditions = call_user_func($oneToManyConfig['conditions'], $entity->get('id'));
            $oneToMany = $this->getOneToMany($oneToManyConfig['type'], $conditions);

            $entity->set($field, $oneToMany);
        }

        return $this;
    }

    /**
     * @param string $type
     * @param Conditions $conditions
     * @return Collection
     */
    protected function getOneToMany($type, Conditions $conditions = null)
    {
        return $this->mapperFactory->createForType($type)->findAll($type, $conditions);
    }

}