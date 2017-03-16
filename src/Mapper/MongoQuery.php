<?php
namespace Boxspaced\EntityManager\Mapper;

class MongoQuery
{

    /**
     * @var array
     */
    protected $filters;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param array $filters
     * @param array $options
     */
    public function __construct(array $filters = [], array $options = [])
    {
        $this->filters = $filters;
        $this->options = $options;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

}
