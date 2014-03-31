<?php

class EntityManager_Mapper_Conditions_Field
{

    const FOREIGN_SEPARATOR = '.';

    /**
     * @var string
     */
    protected $_name;

    /**
     * @var string
     */
    protected $_operator;

    /**
     * @var mixed
     */
    protected $_value;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->_name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param string $name
     * @return EntityManager_Mapper_Conditions_Field
     */
    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->_operator;
    }

    /**
     * @param string $operator
     * @return EntityManager_Mapper_Conditions_Field
     */
    public function setOperator($operator)
    {
        $this->_operator = $operator;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * @param mixed $value
     * @return EntityManager_Mapper_Conditions_Field
     */
    public function setValue($value)
    {
        $this->_value = $value;
        return $this;
    }

    /**
     * @return bool
     */
    public function isComplete()
    {
        return (!is_null($this->_name)
                && !is_null($this->_operator)
                && !is_null($this->_value));
    }

    /**
     * @return bool
     */
    public function isForeign()
    {
        return strpos($this->_name, self::FOREIGN_SEPARATOR) !== false;
    }

    /**
     * @return string
     */
    public function getForeignPath()
    {
        $parsed = $this->_parseForeignFieldName();
        return $parsed['path'];
    }

    /**
     * @return string
     */
    public function getForeignField()
    {
        $parsed = $this->_parseForeignFieldName();
        return $parsed['field'];
    }

    /**
     * @return array
     * @throws EntityManager_Mapper_Conditions_Exception
     */
    protected function _parseForeignFieldName()
    {
        if (!$this->isForeign($this->_name)) {
            throw new EntityManager_Mapper_Conditions_Exception('Field is not recognised as foreign');
        }

        $exploded = explode(self::FOREIGN_SEPARATOR, $this->_name);
        $field = array_pop($exploded);
        $path = $exploded;

        return array(
            'field' => $field,
            'path' => $path,
        );
    }

}
