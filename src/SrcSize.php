<?php

namespace WP_Image;

class SrcSize
{
    protected $width;
    protected $height;

    public function __construct($width, $height)
    {
        $this->width = round($width);
        $this->height = round($height);
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function resizeToWidth($new_width)
    {
        $ratio = $this->width / $this->height;
        $new_height = $new_width / $ratio;
        return new static($new_width, $new_height);
    }

    public function resizeByFactor($factor)
    {
        return static::resizeToWidth($this->width * $factor);
    }
}