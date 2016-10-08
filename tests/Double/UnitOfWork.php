<?php
namespace Boxspaced\EntityManager\Test\Double;

class UnitOfWork extends \Boxspaced\EntityManager\UnitOfWork
{

    public function __construct()
    {

    }

    protected function process()
    {
        return $this;
    }

}
