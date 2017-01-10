<?php
namespace Boxspaced\EntityManager\Mapper;

use Boxspaced\EntityManager\Mapper\Conditions;

interface ConditionsFactoryInterface
{

    /**
     * @param int $id
     * @param array $options
     * @return Conditions
     */
    public function __invoke($id, array $options = null);

}
