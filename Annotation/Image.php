<?php

namespace EP\DisplayBundle\Annotation;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class Image
{
    private $path;

    private $height;

    private $width;

    public function __construct($options)
    {
        foreach ($options as $key => $value) {
            if (!property_exists($this, $key)) {
                throw new \InvalidArgumentException(sprintf('Property "%s" does not exist', $key));
            }
            $this->$key = $value;
        }
    }

    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->width;
    }
}