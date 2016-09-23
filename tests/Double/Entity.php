<?php
namespace EntityManager\Test\Double;

class Entity implements \EntityManager\Entity\EntityInterface
{

    protected $id;

    protected $title;

    protected $fname;

    protected $lname;

    private static $counter = 0;

    public function __construct()
    {
        $this->id = self::$counter++;
    }

    public function getTypeMap()
    {
        return [];
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function getFname()
    {
        return $this->fname;
    }

    public function setFname($fname)
    {
        $this->fname = $fname;
        return $this;
    }

    public function getLname()
    {
        return $this->lname;
    }

    public function setLname($lname)
    {
        $this->lname = $lname;
        return $this;
    }

}
