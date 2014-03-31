<?php

class EntityStub implements EntityManager_EntityInterface
{

    protected $_id;
    private static $_counter = 0;

    public function getId()
    {
        return $this->_id;
    }

    public function __construct()
    {
        $this->_id = self::$_counter++;
    }

}
