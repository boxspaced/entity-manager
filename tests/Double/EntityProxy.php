<?php
namespace EntityManager\Test\Double;

class EntityProxy extends Entity
{

    protected $initializer;

    protected $entity;

    public function __construct(callable $initializer)
    {
        $this->initializer = $initializer;
    }

    public function getTitle()
    {
        if (null === $this->entity) {
            $this->entity = call_user_func($this->initializer);
        }

        return $this->entity->getTitle();
    }

    public function setTitle($title)
    {
        if (null === $this->entity) {
            $this->entity = call_user_func($this->initializer);
        }

        return $this->entity->setTitle($title);
    }

    public function getFname()
    {
        if (null === $this->entity) {
            $this->entity = call_user_func($this->initializer);
        }

        return $this->entity->getFname();
    }

    public function setFname($fname)
    {
        if (null === $this->entity) {
            $this->entity = call_user_func($this->initializer);
        }

        return $this->entity->setFname($fname);
    }

    public function getLname()
    {
        if (null === $this->entity) {
            $this->entity = call_user_func($this->initializer);
        }

        return $this->entity->getLname();
    }

    public function setLname($lname)
    {
        if (null === $this->entity) {
            $this->entity = call_user_func($this->initializer);
        }

        return $this->entity->setLname($lname);
    }

}
