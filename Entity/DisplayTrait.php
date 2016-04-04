<?php

namespace EP\DisplayBundle\Entity;

/**
 * Class DisplayTrait
 */
trait DisplayTrait
{
    /**
     * get object vars
     *
     * @return array
     */
    public function display()
    {
        return get_object_vars($this);
    }
}