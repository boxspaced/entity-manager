<?php

class DummyUnitOfWorkProcessor implements EntityManager_UnitOfWorkProcessorInterface
{

    public function process(array $new, array $dirty, array $delete)
    {
        return $this;
    }

}
