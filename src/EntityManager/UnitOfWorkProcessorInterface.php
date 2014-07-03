<?php

interface EntityManager_UnitOfWorkProcessorInterface
{

    /**
     * @param EntityManager_EntityInterface[] $new
     * @param EntityManager_EntityInterface[] $dirty
     * @param EntityManager_EntityInterface[] $delete
     * @return void
     */
    public function process(array $new, array $dirty, array $delete);

}
