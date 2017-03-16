<?php
namespace Boxspaced\EntityManager\Mapper;

use Boxspaced\EntityManager\Exception;
use DateTime;
use MongoDB\BSON\UTCDateTime;

class MongoQueryBuilder
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
     * @return MongoQuery
     */
    public function buildFromMapperQuery($type, Query $query = null)
    {
        $filters = [];
        $options = [];

        if (null !== $query) {

            $filters = $this->getFilters($type, $query);
            $options = $this->getOptions($type, $query);
        }

        return new MongoQuery($filters, $options);
    }

    /**
     * @param string $type
     * @param Query $query
     * @return array
     * @throws Exception\UnexpectedValueException
     */
    protected function getFilters($type, Query $query)
    {
        $filters = [];

        foreach ($query->getFields() as $field) {

            $value = $field->getValue();

            if ($value instanceof Expr) {
                throw new Exception\UnexpectedValueException('The Mongo mapper strategy does not support expessions');
            }

            if ($value instanceof DateTime) {
                $value = new UTCDateTime($value->getTimestamp());
            }

            $documentFieldName = $this->getDocumentFieldName($type, $field);

            $operator = $this->convertOperator($field->getOperator());
            $value = ($value === Query::VALUE_NULL) ? null : $value;

            $filters[$documentFieldName] = [$operator => $value];
        }

        return $filters;
    }

    /**
     * @param string $type
     * @param Query $query
     * @return array
     */
    protected function getOptions($type, Query $query)
    {
        $options = [];

        if ($query->getOrder()) {

            $options['sort'] = [];

            foreach ($query->getOrder() as $order) {

                $documentFieldName = $this->getDocumentFieldName($type, $order->getField());
                $direction = ($order->getDirection() === Query::ORDER_ASC) ? 1 : -1;

                $options['sort'][$documentFieldName] = $direction;
            }
        }

        if ($query->getPaging()) {

            $options['limit'] = $query->getPaging()->getShowPerPage();
            $options['skip'] = $query->getPaging()->getOffset();
        }

        return $options;
    }

    /**
     * @param string $operator
     * @return string
     * @throws Exception\InvalidArgumentException
     */
    protected function convertOperator($operator)
    {
        switch ($operator) {
            case Query::OPERATOR_EQUALS:
            case Query::OPERATOR_IS:
                return '$eq';
            case Query::OPERATOR_NOT_EQUALS:
            case Query::OPERATOR_IS_NOT:
                return '$ne';
            case Query::OPERATOR_GREATER_THAN:
                return '$gt';
            case Query::OPERATOR_LESS_THAN:
                return '$lt';
            default:
                throw new Exception\InvalidArgumentException(sprintf(
                    'The Mongo mapper strategy does not support operator: %s',
                    $operator
                ));
        }
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

            // @todo get rid of lcfirst
            $field = lcfirst($part);

            if (!isset($previous['references'][$field])) {

                throw new Exception\UnexpectedValueException(sprintf(
                    'Previous mapping does not reference this part: %s in path: %s',
                    $part,
                    $path
                ));
            }

            // @todo must also all be embedded!

            $type = $previous['references'][$field]['type'];

            $mappings[] = $this->createMapping($type);
        }

        if (count($mappings) === 1) {
            return $mappings[0];
        }

        return $mappings;
    }

    /**
     * @param string $type
     * @return array
     * @throws Exception\UnexpectedValueException
     */
    protected function createMapping($type)
    {
        if (!isset($this->config['types'][$type])) {
            throw new Exception\InvalidArgumentException("Config missing for type: {$type}");
        }

        $config = $this->config['types'][$type];

        $mapping = [];
        $mapping['document_fields'] = [];
        $mapping['references'] = [];

        if (isset($config['mapper']['params']['document_fields'])) {
            $mapping['document_fields'] = $config['mapper']['params']['document_fields'];
        }

        if (isset($config['entity']['fields'])) {
            $mapping['references'] = $config['entity']['fields'];
        }

        return $mapping;
    }

    /**
     * @param string $type
     * @param Field $field
     * @return string
     */
    protected function getDocumentFieldName($type, Field $field)
    {
        $documentFieldName = '';

        if ($field->isForeign()) {

            $fieldName = $field->getForeignField();
            $foreignPath = $field->getForeignPath();

            $mappings = $this->getMappings(sprintf(
                '%s.%s',
                $type,
                implode('.', $foreignPath)
            ));

            foreach ($foreignPath as $key => $part) {
                
                $documentFieldName .= (
                    isset($mappings[$key]['document_fields'][$part])
                    ? $mappings[$key]['document_fields'][$part]
                    : $part
                ) . '.';
            }

            $mapping = array_pop($mappings);

        } else {

            $fieldName = $field->getName();
            $mapping = $this->getMappings($type);
        }

        $fieldName = lcfirst($fieldName);

        if (!isset($mapping['references'][$fieldName])) {

            throw new Exception\InvalidArgumentException(sprintf(
                'Entity does not have field: %s',
                $fieldName
            ));
        }

        if (isset($mapping['document_fields'][$fieldName])) {
            $documentFieldName .= $mapping['document_fields'][$fieldName];
        } else {
            $documentFieldName .= $fieldName;
        }

        return $documentFieldName;
    }

}
