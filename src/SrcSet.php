<?php

namespace WP_Image;


class SrcSet
{

    static protected $multipliers = [1, 2/3, 0.5, 1/3];

    protected $default_size;

    protected $source_sizes = [];
    protected $viewport_sizes = [];

    public function __construct(SrcSize $full_size, $breakpoints = [])
    {
        $this->default_size = $full_size;
        foreach (static::$multipliers as $multiplier) {
            $this->source_sizes[] = $full_size->resizeByFactor($multiplier);
        }
        if (!is_array($breakpoints)) $breakpoints = [$breakpoints];
        foreach ($breakpoints as $breakpoint) {
            $this->viewport_sizes[] = $breakpoint;
        }
        if (!empty($this->viewport_sizes)) {
            $this->viewport_sizes[] = '100vw';
        }

    }

    public function getDefaultSize()
    {
        return $this->default_size;
    }

    public function getSourceSizes()
    {
        return $this->source_sizes;
    }

    public function getViewportSizes()
    {
        return $this->viewport_sizes;
    }
}