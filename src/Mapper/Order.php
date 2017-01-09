<?php
namespace Boxspaced\EntityManager\Mapper;

class Order
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
     * @param Field $field
     * @param string $direction
     */
    public function __construct(
        Field $field,
        $direction
    )
    {
        $this->field = $field;
        $this->direction = $direction;
    }

    /**
     * @return Field
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
