<?php
namespace Boxspaced\EntityManager\Test\Double;

use Boxspaced\EntityManager\Collection\Collection;

class CollectionFactory extends \Boxspaced\EntityManager\Collection\Factory
{

    public function __construct()
    {

    }

    public function create($type, callable $rowset = null)
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
