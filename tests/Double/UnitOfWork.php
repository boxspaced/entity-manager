<?php
namespace EntityManager\Test\Double;

class UnitOfWork extends \EntityManager\UnitOfWork
{

    public function __construct()
    {

    }

    protected function process()
    {
        return $this;
    }

}
