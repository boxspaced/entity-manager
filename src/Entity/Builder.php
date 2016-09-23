<?php
namespace EntityManager\Entity;

use EntityManager\IdentityMap;
use EntityManager\UnitOfWork;
use EntityManager\Entity\Factory as EntityFactory;
use EntityManager\Mapper\Factory as MapperFactory;
use EntityManager\Entity\AbstractEntity;
use EntityManager\Collection\AbstractCollection as Collection;
use EntityManager\Mapper\Conditions\Conditions;
use Zend\Config\Config;
use UnexpectedValueException;

class Builder
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
     * @var Config
     */
    protected $config;

    /**
     * @param IdentityMap $identityMap
     * @param UnitOfWork $unitOfWork
     * @param EntityFactory $entityFactory
     * @param MapperFactory $mapperFactory
     * @param Config $config
     */
    public function __construct(
        IdentityMap $identityMap,
        UnitOfWork $unitOfWork,
        EntityFactory $entityFactory,
        MapperFactory $mapperFactory,
        Config $config
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
     * @throws UnexpectedValueException
     */
    public function build($type, array $data)
    {
        if (!$data) {
            throw new UnexpectedValueException(
                'Data array empty, use factories to create new entities'
            );
        }

        if (empty($data['id'])) {
            throw new UnexpectedValueException(
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
        $this->setEntityReferences($entity, $data);
        $this->setEntityChildren($entity);

        return $entity;
    }

    /**
     * @param AbstractEntity $entity
     * @param array $data
     * @return Builder
     */
    protected function setEntityFields(AbstractEntity $entity, array $data)
    {
        $methods = get_class_methods($entity);

        foreach ($data as $field => $value) {

            $setter = sprintf('set%s', ucfirst($field));

            if (in_array($setter, $methods)) {
                $this->setEntityField($entity, $field, $value);
            }
        }

        return $this;
    }

    /**
     * @param AbstractEntity $entity
     * @param string $field
     * @param mixed $value
     * @return Builder
     */
    protected function setEntityField(AbstractEntity $entity, $field, $value)
    {
        $typeMap = $entity->getTypeMap();

        if (array_key_exists($field, $typeMap) && !is_null($value)) {

            switch ($typeMap[$field]) {

                case static::TYPE_INT:
                    $value = intval($value);
                    break;

                case static::TYPE_FLOAT:
                    $value = floatval($value);
                    break;

                case static::TYPE_BOOLEAN:
                    $value = boolval($value);
                    break;

                case static::TYPE_DATETIME:
                    $value = new \DateTime($value);
                    break;

                default:
                    // No default leave as string
            }
        }

        $setter = sprintf('set%s', ucfirst($field));
        $entity->{$setter}($value);

        return $this;
    }

    /**
     * @param AbstractEntity $entity
     * @param array $data
     * @return Builder
     */
    protected function setEntityReferences(AbstractEntity $entity, array $data)
    {
        $config = $this->getBuilderConfig(get_class($entity));

        if (null === $config) {
            return $this;
        }

        foreach ($config->get('references', []) as $field => $referenceConfig) {

            $reference = $this->getReference($referenceConfig->type, $data[$field]);

            $setter = sprintf('set%s', ucfirst($field));
            $entity->{$setter}($reference);
        }

        return $this;
    }

    /**
     * @param string $type
     * @return Config
     * @throws UnexpectedValueException
     */
    protected function getBuilderConfig($type)
    {
        if (!isset($this->config->types->{$type})) {
            throw new UnexpectedValueException("No config found for type: {$type}");
        }

        return $this->config->types->{$type}->builder;
    }

    /**
     * @param AbstractEntity $entity
     * @return Builder
     * @throws UnexpectedValueException
     */
    protected function setEntityChildren(AbstractEntity $entity)
    {
        $config = $this->getBuilderConfig(get_class($entity));

        if (null === $config) {
            return $this;
        }

        foreach ($config->get('children', []) as $field => $childrenConfig) {

            if (!is_callable($childrenConfig->conditions)) {
                throw new UnexpectedValueException('The children conditions must be callable');
            }

            $conditions = call_user_func($childrenConfig->conditions, $entity->getId());

            $children = $this->getChildren(
                $childrenConfig->type,
                $conditions
            );

            $setter = sprintf('set%s', ucfirst($field));
            $entity->{$setter}($children);
        }

        return $this;
    }

    /**
     * @param string $type
     * @param int $id
     * @return AbstractEntity
     */
    protected function getReference($type, $id)
    {
        if (!$id) {
            return null;
        }

        $callback = function() use ($type, $id) {
            return $this->mapperFactory->createForType($type)->find($id);
        };
        $callback->bindTo($this);

        return $callback; // @todo new Proxy($callback);
    }

    /**
     * @param string $type
     * @param Conditions $conditions
     * @return Collection
     */
    protected function getChildren($type, Conditions $conditions = null)
    {
        return $this->mapperFactory->createForType($type)->findAll($conditions);
    }

}
