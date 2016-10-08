<?php
namespace Boxspaced\EntityManager\Collection;

use Pimple\Container;

class Factory
{

    /**
     * @var Container
     */
    protected $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $type
     * @param callable $rowset
     * @return Collection
     */
    public function create($type, callable $rowset = null)
    {
        $collection = new Collection(
            $this->container['unitOfWork'],
            $this->container['entityBuilder'],
            $type
        );

        if (null !== $rowset) {
            $collection->setRowset($rowset);
        }

        return $collection;
    }

}
