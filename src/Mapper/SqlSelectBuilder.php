<?php
namespace Boxspaced\EntityManager\Mapper;

use Boxspaced\EntityManager\Exception;
use DateTime;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;

class SqlSelectBuilder
{

    /**
     * @var array
     */
    protected $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $type
     * @param Query $query
     * @return Select
     * @throws Exception\InvalidArgumentException
     */
    public function buildFromMapperQuery($type, Query $query = null)
    {
        if (empty($this->config['types'][$type]['mapper']['params']['table'])) {
            throw new Exception\InvalidArgumentException("Mapper table missing for type: {$type}");
        }

        $select = new Select($this->config['types'][$type]['mapper']['params']['table']);

        if (null !== $query) {

            foreach ($this->getJoins($type, $query) as $join) {
                $select->join([$join['alias'] => $join['table']], $join['fk'], []);
            }

            foreach ($this->getWhere($type, $query) as $where) {
                $select->where($where);
            }

            $select->order($this->getOrder($type, $query));

            if ($query->getPaging()) {

                $select->limit($query->getPaging()->getShowPerPage());
                $select->offset($query->getPaging()->getOffset());
            }
        }

        return $select;
    }

    /**
     * @param string $type
     * @param Query $query
     * @return array
     */
    protected function getJoins($type, Query $query)
    {
        $fields = $query->getFields();

        foreach ($query->getOrder() as $order) {
            $fields[] = $order->getField();
        }

        $joins = [];

        foreach ($fields as $field) {

            if (!$field->isForeign()) {
                continue;
            }

            $mappings = $this->getMappings(sprintf(
                '%s.%s',
                $type,
                implode('.', $field->getForeignPath())
            ));

            foreach ($mappings as $mapping) {

                if (isset($mapping['fk'])) {
                    $joins[$mapping['alias']] = $mapping;
                }
            }
        }

        return $joins;
    }

    /**
     * @param string $path
     * @return array
     * @throws Exception\UnexpectedValueException
     */
    protected function getMappings($path)
    {
        $mappings = [];

        foreach (explode('.', $path) as $part) {

            $previous = end($mappings);

            if (false === $previous) {

                $mappings[] = $this->createMapping($part);
                continue;
            }

            $field = lcfirst($part);

            if (!isset($previous['references'][$field])) {

                throw new Exception\UnexpectedValueException(sprintf(
                    'Previous mapping does not reference this part: %s in path: %s',
                    $part,
                    $path
                ));
            }

            $type = $previous['references'][$field]['type'];

            $mappings[] = $this->createMapping($type, $field, $previous);
        }

        if (1 === count($mappings)) {
            return $mappings[0];
        }

        return $mappings;
    }

    /**
     * @param string $type
     * @param string $field
     * @param array $previous
     * @return array
     * @throws Exception\InvalidArgumentException
     * @throws Exception\UnexpectedValueException
     */
    protected function createMapping($type, $field = null, array $previous = null)
    {
        if (!isset($this->config['types'][$type])) {
            throw new Exception\InvalidArgumentException("Config missing for type: {$type}");
        }

        $config = $this->config['types'][$type];

        if (empty($config['mapper']['params']['table'])) {
            throw new Exception\InvalidArgumentException("Mapper table missing for type: {$type}");
        }

        $mapping = [];
        $mapping['table'] = $config['mapper']['params']['table'];
        $mapping['alias'] = isset($previous['alias']) ? $previous['alias'] . '_' . $field : $field;
        $mapping['columns'] = [];
        $mapping['references'] = [];

        if (isset($config['mapper']['params']['columns'])) {
            $mapping['columns'] = $config['mapper']['params']['columns'];
        }

        if (isset($config['entity']['fields'])) {
            $mapping['references'] = $config['entity']['fields'];
        }

        if (null !== $previous && null !== $field) {

            if (!isset($previous['columns'][$field])) {
                throw new Exception\UnexpectedValueException("No column provided in previous mapping for field: {$field}");
            }

            $mapping['fk'] = sprintf(
                '%s.%s = %s.%s',
                isset($previous['alias']) ? $previous['alias'] : $previous['table'],
                $previous['columns'][$field],
                $mapping['alias'],
                isset($mapping['columns']['id']) ? $mapping['columns']['id'] : 'id'
            );
        }

        return $mapping;
    }

    /**
     * @param string $type
     * @param Query $query
     * @return array
     */
    protected function getWhere($type, Query $query)
    {
        $where = [];

        foreach ($query->getFields() as $field) {

            $value = $field->getValue();

            if ($value instanceof Expr) {
                $value = new Expression($value->__toString());
            }

            if ($value instanceof DateTime) {
                $value = $value->format('Y-m-d H:i:s');
            }

            $column = $this->getColumnName($type, $field);

            $where[] = [
                sprintf('%s %s ?', $column, $field->getOperator()) => $value,
            ];
        }

        return $where;
    }

    /**
     * @param string $type
     * @param Query $query
     * @return array
     */
    protected function getOrder($type, Query $query)
    {
        $orderBy = [];

        if ($query->getOrder()) {

            foreach ($query->getOrder() as $order) {

                $field = $order->getField();
                $column = $this->getColumnName($type, $field);

                $orderBy[$column] = $order->getDirection();
            }
        }

        return $orderBy;
    }

    /**
     * @param string $type
     * @param Field $field
     * @return string
     * @throws Exception\InvalidArgumentException
     */
    protected function getColumnName($type, Field $field)
    {
        if ($field->isForeign()) {

            $fieldName = $field->getForeignField();
            $mappings = $this->getMappings(sprintf(
                '%s.%s',
                $type,
                implode('.', $field->getForeignPath())
            ));
            $mapping = array_pop($mappings);

        } else {

            $fieldName = $field->getName();
            $mapping = $this->getMappings($type);
        }

        $fieldName = lcfirst($fieldName);

//        if (!isset($mapping['references'][$fieldName])) {
//
//            throw new Exception\InvalidArgumentException(sprintf(
//                'Entity does not have field: %s',
//                $fieldName
//            ));
//        }

        $columnName = $fieldName;

        if (isset($mapping['columns'][$fieldName])) {
            $columnName = $mapping['columns'][$fieldName];
        }

        return (isset($mapping['alias']) ? $mapping['alias'] : $mapping['table']) . '.' . $columnName;
    }

}
