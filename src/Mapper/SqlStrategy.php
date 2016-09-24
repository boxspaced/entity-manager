<?php
namespace EntityManager\Mapper;

use EntityManager\Entity\AbstractEntity;
use EntityManager\Mapper\Conditions\Conditions;
use EntityManager\Mapper\Sql\Select;
use Zend\Db\Adapter\AdapterInterface as Database;
use Zend\Db\Sql\Sql;
use Zend\Config\Config;
use Zend\Filter\Word\UnderscoreToCamelCase;
use Zend\Db\Metadata\Metadata;
use Zend\Db\Sql\Where;
use InvalidArgumentException;

class SqlStrategy implements StrategyInterface
{

    /**
     * @var Database
     */
    protected $db;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param Database $db
     * @param Config $config
     */
    public function __construct(Database $db, Config $config)
    {
        $this->db = $db;
        $this->config = $config;
    }

    /**
     * @param string
     * @param int $id
     * @return array
     */
    public function find($type, $id)
    {
        $conditions = (new Conditions())->field('id')->eq($id);

        $select = new Select($this->config, $type, $conditions);
        $stmt = (new Sql($this->db))->prepareStatementForSqlObject($select);

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
        $columns = isset($config->columns) ? $config->columns->toArray() : [];

        $remapped = [];

        foreach ($row as $column => $value) {

            $field = array_search($column, $columns);

            if (false !== $field) {
                $remapped[$field] = $value;
                continue;
            }

            $field = lcfirst((new UnderscoreToCamelCase)->filter($column));

            $remapped[$field] = $value;
        }

        return $remapped;
    }

    /**
     * @param string $type
     * @return Config
     * @throws InvalidArgumentException
     */
    protected function getMapperConfig($type)
    {
        if (!isset($this->config->types->{$type}->mapper->params)) {
            throw new InvalidArgumentException("Mapper config missing for type: {$type}");
        }

        $config = $this->config->types->{$type}->mapper->params;

        if (empty($config->table)) {
            throw new InvalidArgumentException("Mapper table missing for type: {$type}");
        }

        return $config;
    }

    /**
     * @param string
     * @param Conditions $conditions
     * @return array
     */
    public function findOne($type, Conditions $conditions = null)
    {
        $select = new Select($this->config, $type, $conditions);
        $stmt = (new Sql($this->db))->prepareStatementForSqlObject($select);

        $row = $stmt->execute()->current();

        if (false === $row) {
            return null;
        }

        return $this->remapRow($type, $row);
    }

    /**
     * @param string
     * @param Conditions $conditions
     * @return array
     */
    public function findAll($type, Conditions $conditions = null)
    {
        $select = new Select($this->config, $type, $conditions);
        $stmt = (new Sql($this->db))->prepareStatementForSqlObject($select);

        $rows = [];

        foreach ($stmt->execute()->getResource()->fetchAll() as $row) {
            $rows[] = $this->remapRow($type, $row);
        }

        return $rows;
    }

    /**
     * @param AbstractEntity $entity
     * @return SqlMapper
     */
    public function insert(AbstractEntity $entity)
    {
        $config = $this->getMapperConfig(get_class($entity));

        $sql = new Sql($this->db);
        $insert = $sql->insert($config->table);

        $row = $this->entityToRow($entity);
        $insert->columns(array_keys($row));
        $insert->values(array_values($row));

        $stmt = $sql->prepareStatementForSqlObject($insert);
        $stmt->execute();

        $id = $this->db->getDriver()->getConnection()->getLastGeneratedValue();
        $entity->setId($id);

        return $this;
    }

    /**
     * @param AbstractEntity $entity
     * @return SqlMapper
     */
    public function update(AbstractEntity $entity)
    {
        $config = $this->getMapperConfig(get_class($entity));

        $sql = new Sql($this->db);
        $update = $sql->update($config->table);

        $row = $this->entityToRow($entity);
        $update->set($row);

        $where = (new Where())->equalTo(
            isset($config->columns->id) ? $config->columns->id : 'id',
            $entity->get('id')
        );
        $update->where($where);

        $stmt = $sql->prepareStatementForSqlObject($update);
        $stmt->execute();

        return $this;
    }

    /**
     * @param AbstractEntity $entity
     * @return SqlMapper
     */
    public function delete(AbstractEntity $entity)
    {
        $config = $this->getMapperConfig(get_class($entity));

        $sql = new Sql($this->db);
        $delete = $sql->delete($config->table);

        $where = (new Where())->equalTo(
            isset($config->columns->id) ? $config->columns->id : 'id',
            $entity->get('id')
        );
        $delete->where($where);

        $stmt = $sql->prepareStatementForSqlObject($delete);
        $stmt->execute();

        return $this;
    }

    /**
     * @param AbstractEntity $entity
     * @return array
     */
    protected function entityToRow(AbstractEntity $entity)
    {
        $config = $this->getMapperConfig(get_class($entity));

        $metadata = new Metadata($this->db);
        $table = $metadata->getTable($config->table);
        $columns = isset($config->columns) ? $config->columns->toArray() : [];

        $row = [];

        // @todo stop call to db to get columns cos have fields in config now

        foreach ($table->getColumns() as $column) {

            $field = array_search($column, $columns);

            if (false === $field) {
                $field = (new UnderscoreToCamelCase)->filter($column);
            }

            if (!$entity->has($field)) {
                continue;
            }

            $value = $entity->get($field);

            if ($value instanceof \DateTime) {
                $value = $value->format('Y-m-d H:i:s');
            }

            if ($value instanceof \EntityManager\Entity\AbstractEntity) {
                $value = $value->get('id');
            }

            $row[$column] = $value;
        }

        return $row;
    }

}
