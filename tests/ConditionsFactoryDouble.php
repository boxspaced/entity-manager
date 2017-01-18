<?php
namespace Boxspaced\EntityManager\Test;

use Boxspaced\EntityManager\Mapper\ConditionsFactoryInterface;
use Boxspaced\EntityManager\Mapper\Conditions;

class ConditionsFactoryDouble implements ConditionsFactoryInterface
{

    public $id;

    public $options;

    public function __invoke($id, array $options = null)
    {
        $this->id = $id;
        $this->options = $options;

        return new Conditions();
    }

}
