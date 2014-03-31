<?php

class EntityStub implements EntityManager_EntityInterface
{

    protected $id;
    private static $counter = 0;

    public function getId()
    {
        return $this->id;
    }

    public function __construct()
    {
        $this->id = self::$counter++;
    }

}
