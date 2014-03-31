<?php

class EntityManager_Mapper_Conditions_Expr
{

    /**
     * @var string
     */
    protected $expression;

    /**
     * @param string $expression
     */
    public function __construct($expression)
    {
        $this->expression = (string) $expression;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->expression;
    }

}
