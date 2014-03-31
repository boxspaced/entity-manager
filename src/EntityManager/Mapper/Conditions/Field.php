<?php

class EntityManager_Mapper_Conditions_Field
{

    const FOREIGN_SEPARATOR = '.';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $operator;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return EntityManager_Mapper_Conditions_Field
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @param string $operator
     * @return EntityManager_Mapper_Conditions_Field
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return EntityManager_Mapper_Conditions_Field
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return bool
     */
    public function isComplete()
    {
        return (!is_null($this->name)
                && !is_null($this->operator)
                && !is_null($this->value));
    }

    /**
     * @return bool
     */
    public function isForeign()
    {
        return strpos($this->name, self::FOREIGN_SEPARATOR) !== false;
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
        if (!$this->isForeign($this->name)) {
            throw new EntityManager_Mapper_Conditions_Exception('Field is not recognised as foreign');
        }

        $exploded = explode(self::FOREIGN_SEPARATOR, $this->name);
        $field = array_pop($exploded);
        $path = $exploded;

        return array(
            'field' => $field,
            'path' => $path,
        );
    }

}
