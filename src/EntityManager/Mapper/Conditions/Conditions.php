<?php

class EntityManager_Mapper_Conditions_Conditions
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
     * @var EntityManager_Mapper_Conditions_Field[]
     */
    protected $fields = array();

    /**
     * @var EntityManager_Mapper_Conditions_Order[]
     */
    protected $order = array();

    /**
     * @var EntityManager_Mapper_Conditions_Paging
     */
    protected $paging;

    /**
     * @param string $fieldName
     * @return EntityManager_Mapper_Conditions_Conditions
     * @throws EntityManager_Mapper_Conditions_Exception
     */
    public function field($fieldName)
    {
        $field = end($this->fields);
        if ($field && !$field->isComplete()) {
            throw new EntityManager_Mapper_Conditions_Exception('Last field not completed');
        }
        $field = new EntityManager_Mapper_Conditions_Field($fieldName);
        $this->fields[] = $field;
        return $this;
    }

    /**
     * @param mixed $value
     * @return EntityManager_Mapper_Conditions_Conditions
     */
    public function eq($value)
    {
        $this->_addOperatorAndValueToLastField(self::OPERATOR_EQUALS, $value);
        return $this;
    }

    /**
     * @param mixed $value
     * @return EntityManager_Mapper_Conditions_Conditions
     */
    public function notEq($value)
    {
        $this->_addOperatorAndValueToLastField(self::OPERATOR_NOT_EQUALS, $value);
        return $this;
    }

    /**
     * @return EntityManager_Mapper_Conditions_Conditions
     */
    public function isNull()
    {
        $this->_addOperatorAndValueToLastField(self::OPERATOR_IS, new EntityManager_Mapper_Conditions_Expr(self::VALUE_NULL));
        return $this;
    }

    /**
     * @return EntityManager_Mapper_Conditions_Conditions
     */
    public function isNotNull()
    {
        $this->_addOperatorAndValueToLastField(self::OPERATOR_IS_NOT, new EntityManager_Mapper_Conditions_Expr(self::VALUE_NULL));
        return $this;
    }

    /**
     * @param mixed $value
     * @return EntityManager_Mapper_Conditions_Conditions
     */
    public function gt($value)
    {
        $this->_addOperatorAndValueToLastField(self::OPERATOR_GREATER_THAN, $value);
        return $this;
    }

    /**
     * @param mixed $value
     * @return EntityManager_Mapper_Conditions_Conditions
     */
    public function lt($value)
    {
        $this->_addOperatorAndValueToLastField(self::OPERATOR_LESS_THAN, $value);
        return $this;
    }

    /**
     * @param string $operator
     * @param mixed $value
     * @return EntityManager_Mapper_Conditions_Conditions
     * @throws EntityManager_Mapper_Conditions_Exception
     */
    protected function _addOperatorAndValueToLastField($operator, $value)
    {
        $field = end($this->fields);
        if ($field) {
            if ($field->isComplete()) {
                throw new EntityManager_Mapper_Conditions_Exception('Field already completed');
            }
            $field->setOperator($operator);
            $field->setValue($value);
        } else {
            throw new EntityManager_Mapper_Conditions_Exception('No fields found, add a field first');
        }
        return $this;
    }

    /**
     * @param string $fieldName
     * @param string $direction
     * @return EntityManager_Mapper_Conditions_Conditions
     * @throws EntityManager_Mapper_Conditions_Exception
     */
    public function order($fieldName, $direction)
    {
        if (!in_array($direction, array(self::ORDER_ASC, self::ORDER_DESC))) {
            throw new EntityManager_Mapper_Conditions_Exception('Invalid direction');
        }

        $field = new EntityManager_Mapper_Conditions_Field($fieldName);
        $this->order[] = new EntityManager_Mapper_Conditions_Order($field, $direction);
        return $this;
    }

    /**
     * @param int $offset
     * @param int $showPerPage
     * @return EntityManager_Mapper_Conditions_Conditions
     */
    public function paging($offset, $showPerPage)
    {
        $offset = intval($offset);
        $showPerPage = intval($showPerPage) ?: 10;
        $this->paging = new EntityManager_Mapper_Conditions_Paging($offset, $showPerPage);
        return $this;
    }

    /**
     * @return EntityManager_Mapper_Conditions_Field[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return EntityManager_Mapper_Conditions_Order[]
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return EntityManager_Mapper_Conditions_Paging
     */
    public function getPaging()
    {
        return $this->paging;
    }

}
