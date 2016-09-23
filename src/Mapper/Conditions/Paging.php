<?php
namespace EntityManager\Mapper\Conditions;

class Paging
{

    /**
     * @var int
     */
    protected $offset;

    /**
     * @var int
     */
    protected $showPerPage;

    /**
     * @param int $offset
     * @param int $showPerPage
     */
    public function __construct($offset, $showPerPage)
    {
        $this->offset = (int) $offset;
        $this->showPerPage = (int) $showPerPage;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @return int
     */
    public function getShowPerPage()
    {
        return $this->showPerPage;
    }

}
