<?php
/**
 * Class Placeholder
 * @package WP_Image
 * @author  DevAnime <devanimecards@gmail.com>
 * @version 1.0
 */

namespace WP_Image;

class Placeholder
{
    protected $baseUrl = 'https://placeimg.com';
    protected $width;
    protected $height;
    protected $category;
    protected $filter;

    public function __construct($width = 200, $height = 200, $category = 'any', $filter = '')
    {
        $this->setWidth($width)->setHeight($height)->setCategory($category)->setFilter($filter);
    }

    public function __toString(): string
    {
        return implode('/', array_filter([
                $this->baseUrl,
                $this->width,
                $this->height,
                $this->category,
                $this->filter
            ])
        );
    }

    /**
     * @param mixed $width
     *
     * @return Placeholder
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @param mixed $height
     *
     * @return Placeholder
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @param mixed $category
     *
     * @return Placeholder
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @param mixed $filter
     *
     * @return Placeholder
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }
}