<?php
namespace Boxspaced\EntityManager\Test;

use Boxspaced\EntityManager\Collection\Collection;

class CollectionDouble extends Collection
{

    public function setElements(array $elements)
    {
        $this->elements = $elements;
    }

}
