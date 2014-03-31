<?php

class EntityManager_Mapper_Conditions_Order
{

    /**
     * @var string
     */
    protected $field;

    /**
     * @var string
     */
    protected $direction;

    /**
     * @param EntityManager_Mapper_Conditions_Field $field
     * @param string $direction
     */
    public function __construct(
        EntityManager_Mapper_Conditions_Field $field,
        $direction
    )
    {
        $this->field = $field;
        $this->direction = $direction;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return string
     */
    public function getDirection()
    {
        return $this->direction;
    }

}
