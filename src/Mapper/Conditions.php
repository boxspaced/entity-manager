<?php
namespace Boxspaced\EntityManager\Mapper;

use Boxspaced\EntityManager\Exception;

class Conditions
{

    const OPERATOR_EQUALS = '=';
    const OPERATOR_NOT_EQUALS = '!=';
    const OPERATOR_IS = 'IS';
    const OPERATOR_IS_NOT = 'IS NOT';
    const OPERATOR_GREATER_THAN = '>';
    const OPERATOR_LESS_THAN = '<';
    const VALUE_NULL = 'NULL';
    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';

    /**
     * @var Field[]
     */
    protected $fields = [];

    /**
     * @var Order[]
     */
    protected $order = [];

    /**
     * @var Paging
     */
    protected $paging;

    /**
     * @param string $fieldName
     * @return Conditions
     * @throws Exception\UnexpectedValueException
     */
    public function field($fieldName)
    {
        $field = end($this->fields);

        if ($field && !$field->isComplete()) {
            throw new Exception\UnexpectedValueException('Last field not completed');
        }

        $field = new Field($fieldName);
        $this->fields[] = $field;

        return $this;
    }

    /**
     * @param mixed $value
     * @return Conditions
     */
    public function eq($value)
    {
        $this->addOperatorAndValueToLastField(self::OPERATOR_EQUALS, $value);
        return $this;
    }

    /**
     * @param mixed $value
     * @return Conditions
     */
    public function notEq($value)
    {
        $this->addOperatorAndValueToLastField(self::OPERATOR_NOT_EQUALS, $value);
        return $this;
    }

    /**
     * @return Conditions
     */
    public function isNull()
    {
        $this->addOperatorAndValueToLastField(self::OPERATOR_IS, new Expr(self::VALUE_NULL));
        return $this;
    }

    /**
     * @return Conditions
     */
    public function isNotNull()
    {
        $this->addOperatorAndValueToLastField(self::OPERATOR_IS_NOT, new Expr(self::VALUE_NULL));
        return $this;
    }

    /**
     * @param mixed $value
     * @return Conditions
     */
    public function gt($value)
    {
        $this->addOperatorAndValueToLastField(self::OPERATOR_GREATER_THAN, $value);
        return $this;
    }

    /**
     * @param mixed $value
     * @return Conditions
     */
    public function lt($value)
    {
        $this->addOperatorAndValueToLastField(self::OPERATOR_LESS_THAN, $value);
        return $this;
    }

    /**
     * @param string $operator
     * @param mixed $value
     * @return Conditions
     * @throws Exception\UnexpectedValueException
     */
    protected function addOperatorAndValueToLastField($operator, $value)
    {
        $field = end($this->fields);

        if ($field) {

            if ($field->isComplete()) {
                throw new Exception\UnexpectedValueException('Field already completed');
            }

            $field->setOperator($operator);
            $field->setValue($value);

        } else {
            throw new Exception\UnexpectedValueException('No fields found, add a field first');
        }

        return $this;
    }

    /**
     * @param string $fieldName
     * @param string $direction
     * @return Conditions
     * @throws Exception\InvalidArgumentException
     */
    public function order($fieldName, $direction)
    {
        if (!in_array($direction, [self::ORDER_ASC, self::ORDER_DESC])) {
            throw new Exception\InvalidArgumentException('Invalid direction');
        }

        $field = new Field($fieldName);
        $this->order[] = new Order($field, $direction);

        return $this;
    }

    /**
     * @param int $offset
     * @param int $showPerPage
     * @return Conditions
     */
    public function paging($offset, $showPerPage)
    {
        $offset = intval($offset);
        $showPerPage = intval($showPerPage) ?: 10;

        $this->paging = new Paging($offset, $showPerPage);

        return $this;
    }

    /**
     * @return Field[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return Order[]
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return Paging
     */
    public function getPaging()
    {
        return $this->paging;
    }

}
