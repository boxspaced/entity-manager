<?php
namespace EntityManager;

use Zend\Db\Adapter\AdapterInterface as Database;
use EntityManager\Mapper\Factory as MapperFactory;
use EntityManager\Entity\EntityInterface;
use Exception;

class UnitOfWork
{

    /**
     * @var Database
     */
    protected $db;

    /**
     * @var MapperFactory
     */
    protected $mapperFactory;

    /**
     * @var EntityInterface[]
     */
    protected $new = [];

    /**
     * @var EntityInterface[]
     */
    protected $dirty = [];

    /**
     * @var EntityInterface[]
     */
    protected $delete = [];

    /**
     * @param MapperFactory $mapperFactory
     */
    public function __construct(MapperFactory $mapperFactory)
    {
        $this->mapperFactory = $mapperFactory;
    }

    /**
     * @param Database $db
     * @return UnitOfWork
     */
    public function setDb(Database $db)
    {
        $this->db = $db;
        return $this;
    }

    /**
     * @param EntityInterface $entity
     * @return UnitOfWork
     */
    public function persist(EntityInterface $entity)
    {
        if (!in_array($entity, $this->new, true)) {
            $this->new[] = $entity;
        }

        return $this;
    }

    /**
     * @param EntityInterface $entity
     * @return UnitOfWork
     */
    public function dirty(EntityInterface $entity)
    {
        if (!in_array($entity, $this->new, true)) {
            $this->dirty[$this->globalKey($entity)] = $entity;
        }

        return $this;
    }

    /**
     * @param EntityInterface $entity
     * @return UnitOfWork
     */
    public function delete(EntityInterface $entity)
    {
        $this->delete[$this->globalKey($entity)] = $entity;
        return $this;
    }

    /**
     * @param EntityInterface $entity
     * @return UnitOfWork
     */
    public function clean(EntityInterface $entity)
    {
        unset($this->delete[$this->globalKey($entity)]);
        unset($this->dirty[$this->globalKey($entity)]);

        foreach ($this->new as $key => $value) {
            if ($value === $entity) {
                unset($this->new[$key]);
            }
        }

        return $this;
    }

    /**
     * @return UnitOfWork
     */
    public function flush()
    {
        $this->process();
        $this->new = [];
        $this->dirty = [];
        $this->delete = [];
        return $this;
    }

    /**
     * @return UnitOfWork
     * @throws Exception
     */
    protected function process()
    {
        if (null !== $this->db) {
            $this->db->getDriver()->getConnection()->beginTransaction();
        }

        try {

            foreach ($this->new as $entity) {
                $this->mapperFactory->createForType(get_class($entity))->insert($entity);
            }

            foreach ($this->dirty as $entity) {
                $this->mapperFactory->createForType(get_class($entity))->update($entity);
            }

            foreach ($this->delete as $entity) {
                $this->mapperFactory->createForType(get_class($entity))->delete($entity);
            }

        } catch (Exception $e) {

            if (null !== $this->db) {
                $this->db->getDriver()->getConnection()->rollback();
            }

            throw $e;
        }

        if (null !== $this->db) {
            $this->db->getDriver()->getConnection()->commit();
        }

        return $this;
    }

    /**
     * @param EntityInterface $entity
     * @return string
     */
    protected function globalKey(EntityInterface $entity)
    {
        $key = get_class($entity) . '.' . $entity->getId();
        return $key;
    }

}
