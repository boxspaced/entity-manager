<?php
namespace Boxspaced\EntityManager\Mapper;

use Boxspaced\EntityManager\Entity\AbstractEntity;
use Boxspaced\EntityManager\Exception;
use Zend\Db\Adapter\AdapterInterface as Database;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use DateTime;

class SqlMapperStrategy implements MapperStrategyInterface
{

    /**
     * @var Database
     */
    protected $db;

    /**
     * @var Sql
     */
    protected $sql;

    /**
     * @var array
     */
    protected $config;

    /**
     * @param Database $db
     * @param array $config
     */
    public function __construct(Database $db, array $config)
    {
        $this->db = $db;
        $this->sql = new Sql($db);
        $this->config = $config;
    }

    /**
     * @param string
     * @param int $id
     * @return array
     */
    public function find($type, $id)
    {
        $query = (new Query())->field('id')->eq($id);

        $select = new Select($this->config, $type, $query);
        $stmt = $this->sql->prepareStatementForSqlObject($select);

        $row = $stmt->execute()->current();

        if (false === $row) {
            return null;
        }

        return $this->remapRow($type, $row);
    }

    /**
     * @param string $type
     * @param array $row
     * @return array
     */
    protected function remapRow($type, array $row)
    {
        $config = $this->getMapperConfig($type);
        $columns = isset($config['columns']) ? $config['columns'] : [];

        $remapped = [];

        foreach ($row as $column => $value) {

            $field = array_search($column, $columns);

            if (false !== $field) {
                $remapped[$field] = $value;
                continue;
            }

            $remapped[$column] = $value;
        }

        return $remapped;
    }

    /**
     * @param string $type
     * @return array
     * @throws Exception\InvalidArgumentException
     */
    protected function getMapperConfig($type)
    {
        if (!isset($this->config['types'][$type]['mapper']['params'])) {
            throw new Exception\InvalidArgumentException("Mapper config missing for type: {$type}");
        }

        $config = $this->config['types'][$type]['mapper']['params'];

        if (empty($config['table'])) {
            throw new Exception\InvalidArgumentException("Mapper table missing for type: {$type}");
        }

        return $config;
    }

    /**
     * @param string
     * @param Query $query
     * @return array
     */
    public function findOne($type, Query $query = null)
    {
        $select = new Select($this->config, $type, $query);
        $stmt = $this->sql->prepareStatementForSqlObject($select);

        $row = $stmt->execute()->current();

        if (false === $row) {
            return null;
        }

        return $this->remapRow($type, $row);
    }

    /**
     * @param string
     * @param Query $query
     * @return array
     */
    public function findAll($type, Query $query = null)
    {
        $select = new Select($this->config, $type, $query);
        $stmt = $this->sql->prepareStatementForSqlObject($select);

        $rows = [];

        foreach ($stmt->execute()->getResource()->fetchAll() as $row) {
            $rows[] = $this->remapRow($type, $row);
        }

        return $rows;
    }

    /**
     * @param AbstractEntity $entity
     * @return SqlMapperStrategy
     */
    public function insert(AbstractEntity $entity)
    {
        $config = $this->getMapperConfig(get_class($entity));

        $insert = $this->sql->insert($config['table']);

        $row = $this->entityToRow($entity);
        $insert->columns(array_keys($row));
        $insert->values(array_values($row));

        $stmt = $this->sql->prepareStatementForSqlObject($insert);
        $stmt->execute();

        $id = $this->db->getDriver()->getConnection()->getLastGeneratedValue();
        $entity->setId($id);

        return $this;
    }

    /**
     * @param AbstractEntity $entity
     * @return SqlMapperStrategy
     */
    public function update(AbstractEntity $entity)
    {
        $config = $this->getMapperConfig(get_class($entity));

        $update = $this->sql->update($config['table']);

        $row = $this->entityToRow($entity);
        $update->set($row);

        $where = (new Where())->equalTo(
            isset($config['columns']['id']) ? $config['columns']['id'] : 'id',
            $entity->get('id')
        );
        $update->where($where);

        $stmt = $this->sql->prepareStatementForSqlObject($update);
        $stmt->execute();

        return $this;
    }

    /**
     * @param AbstractEntity $entity
     * @return SqlMapperStrategy
     */
    public function delete(AbstractEntity $entity)
    {
        $config = $this->getMapperConfig(get_class($entity));

        $delete = $this->sql->delete($config['table']);

        $where = (new Where())->equalTo(
            isset($config['columns']['id']) ? $config['columns']['id'] : 'id',
            $entity->get('id')
        );
        $delete->where($where);

        $stmt = $this->sql->prepareStatementForSqlObject($delete);
        $stmt->execute();

        return $this;
    }

    /**
     * @param AbstractEntity $entity
     * @return array
     */
    protected function entityToRow(AbstractEntity $entity)
    {
        $mapperConfig = $this->getMapperConfig(get_class($entity));
        $entityConfig = $this->getEntityConfig(get_class($entity));

        $columns = isset($mapperConfig['columns']) ? $mapperConfig['columns'] : [];
        $fields = isset($entityConfig['fields']) ? $entityConfig['fields'] : [];

        $row = [];

        foreach (array_keys($fields) as $field) {

            $column = $field;

            if (isset($columns[$field])) {
                $column = $columns[$field];
            }

            $value = $entity->get($field);

            if ($value instanceof DateTime) {
                $value = $value->format('Y-m-d H:i:s');
            }

            if ($value instanceof AbstractEntity) {
                $value = $value->get('id');
            }

            $row[$column] = $value;
        }

        return $row;
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

}
