<?php

class EntityManager_Mapper_Conditions_Order
{

    /**
     * @var string
     */
    protected $_field;

    /**
     * @var string
     */
    protected $_direction;

    /**
     * @param EntityManager_Mapper_Conditions_Field $field
     * @param string $direction
     */
    public function __construct(
        EntityManager_Mapper_Conditions_Field $field,
        $direction
    )
    {
        $this->_field = $field;
        $this->_direction = $direction;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->_field;
    }

    /**
     * @return string
     */
    public function getDirection()
    {
        return $this->_direction;
    }

}
