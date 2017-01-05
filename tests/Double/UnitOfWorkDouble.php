<?php
namespace Boxspaced\EntityManager\Test\Double;

use Boxspaced\EntityManager\UnitOfWork;

class UnitOfWorkDouble extends UnitOfWork
{

    public function __construct()
    {

    }

    protected function process()
    {
        return $this;
    }

}
