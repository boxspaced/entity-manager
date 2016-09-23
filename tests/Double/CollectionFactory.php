<?php
namespace EntityManager\Test\Double;

use EntityManager\Collection\Collection;

class CollectionFactory extends \EntityManager\Collection\Factory
{

    public function __construct()
    {

    }

    public function create($type, Callable $rowset = null)
    {
        $collection = new Collection(
            new UnitOfWork(),
            new EntityBuilder(),
            $type
        );

        if (null !== $rowset) {
            $collection->setRowset($rowset);
        }

        return $collection;
    }

}
