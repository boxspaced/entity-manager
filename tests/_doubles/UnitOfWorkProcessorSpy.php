<?php

class UnitOfWorkProcessorSpy implements EntityManager_UnitOfWorkProcessorInterface
{

    public $new;
    public $dirty;
    public $delete;

    public function process(array $new, array $dirty, array $delete)
    {
        $this->new = $new;
        $this->dirty = $dirty;
        $this->delete = $delete;
        return $this;
    }

}
