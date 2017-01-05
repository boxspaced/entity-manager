<?php
namespace Boxspaced\EntityManager\Mapper\Sql;

use Zend\Db\Sql\Expression;
use Zend\Filter\Word\CamelCaseToUnderscore;
use Zend\Config\Config;
use Zend\Db\Sql\Select as ZendSelect;
use Boxspaced\EntityManager\Mapper\Conditions\Conditions;
use Boxspaced\EntityManager\Mapper\Conditions\Field;
use Boxspaced\EntityManager\Mapper\Conditions\Expr;
use UnexpectedValueException;
use InvalidArgumentException;
use DateTime;

class Select extends ZendSelect
{

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var Conditions
     */
    protected $conditions;

    /**
     * @param Config $config
     * @param string $type
     * @param Conditions $conditions
     * @throws UnexpectedValueException
     */
    public function __construct(Config $config, $type, Conditions $conditions = null)
    {
        $this->config = $config;
        $this->type = $type;
        $this->conditions = $conditions;

        if (empty($config->types->{$type}->mapper->params->table)) {
            throw new InvalidArgumentException("Mapper table missing for type: {$type}");
        }

        parent::__construct($config->types->{$type}->mapper->params->table);

        $this->build();
    }

    /**
     * @return Select
     */
    protected function build()
    {
        if (null !== $this->conditions) {

            $this->buildJoins();
            $this->buildWhere();
            $this->buildOrderBy();
            $this->buildLimit();
        }

        return $this;
    }

    /**
     * @return Select
     */
    protected function buildJoins()
    {
        $fields = $this->conditions->getFields();

        foreach ($this->conditions->getOrder() as $order) {
            $fields[] = $order->getField();
        }

        $joins = [];

        foreach ($fields as $field) {

            if (!$field->isForeign()) {
                continue;
            }

            $mappings = $this->getMappings(sprintf(
                '%s.%s',
                $this->type,
                implode('.', $field->getForeignPath())
            ));

            foreach ($mappings as $mapping) {

                if (isset($mapping['fk'])) {
                    $joins[$mapping['alias']] = $mapping;
                }
            }
        }

        foreach ($joins as $mapping) {
            $this->join([$mapping['alias'] => $mapping['table']], $mapping['fk'], []);
        }

        return $this;
    }

    /**
     * @param string $path
     * @return array
     * @throws UnexpectedValueException
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

                throw new UnexpectedValueException(sprintf(
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
     * @throws UnexpectedValueException
     */
    protected function createMapping($type, $field = null, array $previous = null)
    {
        if (!isset($this->config->types->{$type})) {
            throw new InvalidArgumentException("Config missing for type: {$type}");
        }

        $config = $this->config->types->{$type};

        if (empty($config->mapper->params->table)) {
            throw new InvalidArgumentException("Mapper table missing for type: {$type}");
        }

        $mapping = [];
        $mapping['table'] = $config->mapper->params->table;
        $mapping['alias'] = isset($previous['alias']) ? $previous['alias'] . '_' . $field : $field;
        $mapping['columns'] = [];
        $mapping['references'] = [];

        if (isset($config->mapper->params->columns)) {
            $mapping['columns'] = $config->mapper->params->columns->toArray();
        }

        if (isset($config->entity->fields)) {
            $mapping['references'] = $config->entity->fields->toArray();
        }

        if (null !== $previous && null !== $field) {

            if (!isset($previous['columns'][$field])) {
                throw new UnexpectedValueException("No column provided in previous mapping for field: {$field}");
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
     * @return Select
     */
    protected function buildWhere()
    {
        foreach ($this->conditions->getFields() as $field) {

            $value = $field->getValue();

            if ($value instanceof Expr) {
                $value = new Expression($value->__toString());
            }

            if ($value instanceof DateTime) {
                $value = $value->format('Y-m-d H:i:s');
            }

            $column = $this->getColumnName($field);

            $this->where([
                sprintf('%s %s ?', $column, $field->getOperator()) => $value,
            ]);
        }

        return $this;
    }

    /**
     * @return Select
     */
    protected function buildOrderBy()
    {
        if ($this->conditions->getOrder()) {

            $orderBy = [];

            foreach ($this->conditions->getOrder() as $order) {

                $field = $order->getField();
                $column = $this->getColumnName($field);

                $orderBy[$column] = $order->getDirection();
            }

            $this->order($orderBy);
        }

        return $this;
    }

    /**
     * @return Select
     */
    protected function buildLimit()
    {
        if ($this->conditions->getPaging()) {

            $this->limit($this->conditions->getPaging()->getShowPerPage());
            $this->offset($this->conditions->getPaging()->getOffset());
        }

        return $this;
    }

    /**
     * @param Field $field
     * @return string
     */
    protected function getColumnName(Field $field)
    {
        if ($field->isForeign()) {

            $fieldName = $field->getForeignField();
            $mappings = $this->getMappings(sprintf(
                '%s.%s',
                $this->type,
                implode('.', $field->getForeignPath())
            ));
            $mapping = array_pop($mappings);

        } else {

            $fieldName = $field->getName();
            $mapping = $this->getMappings($this->type);
        }

        $fieldName = lcfirst($fieldName);

        if (isset($mapping['columns'][$fieldName])) {
            $columnName = $mapping['columns'][$fieldName];
        } else {
            $columnName = strtolower((new CamelCaseToUnderscore())->filter($fieldName));
        }

        return (isset($mapping['alias']) ? $mapping['alias'] : $mapping['table']) . '.' . $columnName;
    }

}
