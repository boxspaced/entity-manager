<?php
namespace Boxspaced\EntityManager\Mapper\Conditions;

class Expr
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
