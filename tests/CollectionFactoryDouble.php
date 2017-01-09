<?php
namespace Boxspaced\EntityManager\Test;

use Boxspaced\EntityManager\Collection\CollectionFactory;
use Boxspaced\EntityManager\Collection\Collection;

class CollectionFactoryDouble extends CollectionFactory
{

    public function __construct()
    {

    }

    public function create($type, callable $rowset = null)
    {
        $collection = new Collection(
            new UnitOfWorkDouble(),
            new EntityBuilderDouble(),
            $type
        );

        if (null !== $rowset) {
            $collection->setRowset($rowset);
        }

        return $collection;
    }

}
