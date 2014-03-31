<?php

class EntityManager_Mapper_Conditions_Expr
{

    /**
     * @var string
     */
    protected $_expression;

    /**
     * @param string $expression
     */
    public function __construct($expression)
    {
        $this->_expression = (string) $expression;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->_expression;
    }

}
