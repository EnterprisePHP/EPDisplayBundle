<?php

namespace EP\DisplayBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Display extends Annotation
{
    public $image_render;

    public $file_render;

    public $template;

    public $exclude_vars;

    public $array_collection_render;

    public $collection_item_count;
}