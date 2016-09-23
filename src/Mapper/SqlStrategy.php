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
use UnexpectedValueException;

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
        $params = $this->getMapperParams($type);
        $columns = isset($params->columns) ? $params->columns->toArray() : [];

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
     * @throws UnexpectedValueException
     */
    protected function getMapperParams($type)
    {
        if (!isset($this->config->types->{$type}->mapper->params)) {
            throw new UnexpectedValueException('No mapper params found for type');
        }

        $params = $this->config->types->{$type}->mapper->params;

        if (empty($params->table)) {
            throw new UnexpectedValueException('No table provided in mapper params');
        }

        return $params;
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
        $params = $this->getMapperParams(get_class($entity));

        $sql = new Sql($this->db);
        $insert = $sql->insert($params->table);

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
        $params = $this->getMapperParams(get_class($entity));

        $sql = new Sql($this->db);
        $update = $sql->update($params->table);

        $row = $this->entityToRow($entity);
        $update->set($row);

        $where = (new Where())->equalTo(
            isset($params->columns->id) ? $params->columns->id : 'id',
            $entity->getId()
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
        $params = $this->getMapperParams(get_class($entity));

        $sql = new Sql($this->db);
        $delete = $sql->delete($params->table);

        $where = (new Where())->equalTo(
            isset($params->columns->id) ? $params->columns->id : 'id',
            $entity->getId()
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
        $params = $this->getMapperParams(get_class($entity));

        $metadata = new Metadata($this->db);
        $table = $metadata->getTable($params->table);
        $methods = get_class_methods($entity);
        $columns = isset($params->columns) ? $params->columns->toArray() : [];

        $row = [];

        foreach ($table->getColumns() as $column) {

            $field = array_search($column, $columns);

            if (false === $field) {
                $field = (new UnderscoreToCamelCase)->filter($column);
            }

            $getter = sprintf('get%s', ucfirst($field));

            if (!in_array($getter, $methods)) {
                continue;
            }

            $value = $entity->{$getter}();

            if ($value instanceof \DateTime) {
                $value = $value->format('Y-m-d H:i:s');
            }

            if ($value instanceof \EntityManager\Entity\AbstractEntity) {
                $value = $value->getId();
            }

            $row[$column] = $value;
        }

        return $row;
    }

}
