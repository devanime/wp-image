<?php

namespace WP_Image;

class Breakpoint
{
    /* Bootstrap defaults */
    const XS = 'xs';
    const SM = 'sm';
    const MD = 'md';
    const LG = 'lg';

    static protected $breakpoints = [
        self::XS => 480,
        self::SM => 768,
        self::MD => 992,
        self::LG => 1200
    ];

    protected $width_limit;
    protected $width;
    protected $max = false;

    public function __construct($width_limit, $width, $max = false)
    {
        if (is_numeric($width)) {
            $width .= 'px';
        }
        $this->width = $width;

        if (is_numeric($width_limit)) {
            $width_limit .= 'px';
        }
        $this->width_limit = $width_limit;

        $this->max = (bool) $max;
    }

    public static function large($width)
    {
        return new static(static::$breakpoints[static::LG], $width);
    }

    public static function medium($width)
    {
        return new static(static::$breakpoints[static::MD], $width);
    }

    public static function small($width)
    {
        return new static(static::$breakpoints[static::SM], $width);
    }

    public static function extrasmall($width)
    {
        return new static(static::$breakpoints[static::XS], $width);
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getWidthLimit()
    {
        return $this->width_limit;
    }

    public function getMediaQuery()
    {
        $dir = $this->max ? 'max' : 'min';
        return sprintf('(%s-width: %s) %s', $dir, $this->width_limit, $this->width);
    }

    public function __toString()
    {
        return $this->getMediaQuery();
    }
}