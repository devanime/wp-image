<?php

/**
 * Plugin Name: WP Image
 * Plugin URI: http://www.devanimecards.io
 * Description: The "missing" WP Image object. Grab, resize, and output all over your theme! Usage: WP_Image::get_featured($post_id), or WP_Image::get_by_attachment_id( $attachment_id ).
 * Version: 9999
 * Author: DevAnime
 * Author Email: devanimecards@gmail.com
 * License: GPLv2
 */
if ( ! defined( 'ABSPATH' ) ) {
  exit;
} // Exit if accessed directly

use WP_Image\Placeholder;
use WP_Image\SrcSet;
use WP_Image\SrcSize;
use WP_Image\WPImageAdminController;

new WPImageAdminController();

if ( ! class_exists( 'WP_Image' ) ) {
  /**
   * Class WP_Image
   * @author  DevAnime <devanimecards@gmail.com>
   * @version 1.0
   *
   * @property string $url               Get the current URL of the image
   * @property int    $width             Get the current width of the image
   * @property int    $height            Get the current height of the image
   * @property bool   $crop              Get the crop status of the image
   * @property string $orig_url          Get the original URL of the image
   * @property int    $orig_width        Get the original width of the image
   * @property int    $orig_height       Get the original height of the image
   * @property string $alt               Get the alt text from the WP alt field
   * @property string $title             Get the title from the WP title field
   * @property string $caption           Get the caption from the WP caption field
   * @property string $description       Get the description from the WP description field
   * @property string $css_class             Get the css class of the image
   * @property string $srcset            Get the responsive source set
   * @property string $sizes             Get the sizes attribute
   * @property SrcSet $srcset_obj        Get the srcset object
   * @property Placeholder $placeholder  Placeholder
   *
   */
  class WP_Image {

    protected static $upload_dirs, $upload_urls;
    protected static $upload_key = 'default';

    /**
     * @var int WordPress attachment ID
     */
    public $ID;
    /**
     * @var array Internal property list
     */
    private $_props = array();
    /**
     * @var array img element attributes
     */
    private $_el_attr = array();

    /**
     * @param int $attachment_id WP attachment ID
     */
    protected function __construct( $attachment_id ) {
      $this->ID = $attachment_id;
    }


    /**
     * @param string $alt_text
     *
     * @return WP_Image $this
     */
    public function alt( $alt_text ) {
      $this->_props['alt'] = $alt_text;

      return $this;
    }

    /**
     * @param $new_width
     *
     * @return WP_Image $this
     */
    public function width( $new_width ) {
      $this->_props['width'] = $new_width;

      return $this;
    }

    /**
     * @param $new_height
     *
     * @return WP_Image $this
     */
    public function height( $new_height ) {
      $this->_props['height'] = $new_height;

      return $this;
    }

    /**
     * @param SrcSet $srcset
     *
     * @return WP_Image $this
     */
    public function srcset( SrcSet $srcset ) {
      $this->_props['srcset'] = '';
      $this->_props['srcset_obj'] = $srcset;
      $this->width($srcset->getDefaultSize()->getWidth())->height($srcset->getDefaultSize()->getHeight());
      return $this;
    }

    /**
     * Crop to fit specified width/height. Only valid when both are declared.
     *
     * @param bool|true $crop
     *
     * @return WP_Image $this
     */
    public function crop( $crop = true ) {
      $this->_props['crop'] = $crop;

      return $this;
    }

    public function css_class($classes = '') {
        if (is_array($classes)) {
            $classes = implode(' ', $classes);
        }
        $this->_props['css_class'] = $classes;
        return $this;
    }

    /**
     * Set custom element attribute, ie html5 data attributes, aria attributes, etc.
     *
     * @param $key
     * @param $val
     *
     * @return WP_Image $this
     */
    public function custom_attr( $key, $val ) {
      $this->_el_attr[ sanitize_title( $key ) ] = $val;

      return $this;
    }

      /**
       * Get the value of an attribute
       *
       * @param $key
       * @return mixed
       */
      public function attr($key) {
          return $this->_el_attr[ sanitize_title( $key ) ];
      }

    /**
     * Get ACF field value
     *
     * @param $key
     *
     * @return mixed|null|void
     */
    public function get_field( $key ) {
      if ( function_exists( 'get_field' ) ) {
        return get_field( $key, $this->ID );
      }
      return false;
    }

    /**
     * Build required data from sources, object can be used as function to force fetch data.
     *
     * @param $size
     */
    public function __invoke($size = null) {
        if ($size instanceof SrcSet) {
            $this->srcset($size);
            $size = $size->getDefaultSize();
        }
        if ($size instanceof SrcSize) {
            $this->width($size->getWidth())->height($size->getHeight());
        }
        $this->build();
    }

    /**
     * Build required data from sources, object can be used as function to force fetch data.
     */
    public function build() {
        if (! empty($this->ID)) {
            $this->url;
            $this->resize();
        } elseif (!empty($this->placeholder)) {
            if($this->_props['width'] !== $this->placeholder->getWidth() && $this->_props['height'] !== $this->placeholder->getHeight()) {
                // Change requested dimensions if both have been overridden
                $this->placeholder->setWidth($this->_props['width'])->setHeight($this->_props['height']);
            }
            $this->url = (string) $this->placeholder;
            $this->_props['css_class'] = ($this->_props['css_class'] ?: '') . ' wp-image-placeholder';
        }
    }


    /**
     * Get the final html element
     * @return string
     */
    public function get_html() {
      $this();
      $defaults = array(
        'src'    => $this->url,
        'width'  => $this->width,
        'height' => $this->height,
        'alt'    => $this->alt,
      );
      if ($this->srcset) {
        $defaults['srcset'] = $this->srcset;
        if ($this->sizes) {
          $defaults['sizes'] = $this->sizes;
        }
      }
      if ($this->css_class) {
        $defaults['class'] = $this->css_class;
      }
      $ext = pathinfo($this->url, PATHINFO_EXTENSION);
      if ($ext == 'svg') {
        unset($defaults['width']);
        unset($defaults['height']);
      }
      $attributes = wp_parse_args( $this->_el_attr, $defaults );
      return '<img ' . join( ' ', array_map( function ( $key ) use ( $attributes ) {
        if ( is_bool( $attributes[ $key ] ) ) {
          return $attributes[ $key ] ? $key : '';
        }

        return $key . '="' . esc_attr( $attributes[ $key ] ) . '"';
      }, array_keys( $attributes ) ) ) . ' />';
    }

    /**
     * Magic method alias for get_html(). Allows object to be echoed without calling get_html().
     *
     * @return string
     */
    public function __toString() {
      return $this->get_html();
    }

    /**
     * @param $attr
     */
    public function __get( $attr ) {
      if ( ! isset( $this->_props[ $attr ] ) ) {
        $this->pre_get( $attr );
        if ( ! isset( $this->_props[ $attr ] ) ) {
          $this->get_from_meta( $attr );
        }
      }

      return $this->_props[ $attr ];
    }

    private function pre_get( $attr ) {
      $sources = array(
        'from_src'  => array( 'url', 'width', 'height', 'orig_url', 'orig_width', 'orig_height' ),
        'from_post' => array( 'title', 'caption', 'description' ),
        'from_meta' => array( 'alt' )
      );
      foreach ( $sources as $source => $attributes ) {
        if ( in_array( $attr, $attributes ) ) {
          $func = array( $this, 'get_' . $source );
          if ( is_callable( $func ) ) {
            return call_user_func( $func, $attr );
          }
        }
      }
    }

    private function get_from_src( $attr ) {
      $src = wp_get_attachment_image_src( $this->ID, 'full' );
      $fetched = array(
        'url'         => $src[0],
        'orig_url'    => $src[0],
        'orig_width'  => $src[1],
        'orig_height' => $src[2],
      );
      $this->_props = wp_parse_args( $this->_props, $fetched );
      $this->_props[ $attr ] = isset( $this->_props[ $attr ] ) ? $this->_props[ $attr ] : $this->_props[ 'orig_' . $attr ];

      return $this->_props[ $attr ];
    }

    private function get_from_post( $attr ) {
      $attachment = get_post( $this->ID );
      $fetched = array(
        'title'       => $attachment->post_title,
        'caption'     => $attachment->post_excerpt,
        'description' => $attachment->post_content
      );
      $this->_props = wp_parse_args( $this->_props, $fetched );

      return $this->_props[ $attr ];
    }

    private function get_from_meta( $attr ) {
      $key = ( $attr == 'alt' ) ? '_wp_attachment_image_alt' : $attr;
      $this->_props[ $attr ] = get_post_meta( $this->ID, $key, true );
      if ( $attr == 'alt' && empty( $this->_props[ $attr ] ) ) {
        $this->_props[ $attr ] = $this->title;
      }

      return $this->_props[ $attr ];
    }

    private function generateSrcSet()
    {
        if (empty($this->_props['srcset_obj']) || !empty($this->srcset)) {
            return;
        }
        $srcset_array = [];
        $srcset = $this->_props['srcset_obj']; /* @var SrcSet $srcset */
        foreach ($srcset->getSourceSizes() as $size) {
            $image = static::get_by_attachment_id($this->ID);
            $image($size);
            $srcset_array[] = "$image->url {$image->width}w";
        }
        $this->_props['srcset'] = implode(',', $srcset_array);
        $viewport_sizes = $srcset->getViewportSizes();
        if (!empty($viewport_sizes)) {
            $this->_props['sizes'] = implode(',', $viewport_sizes);
        }
    }

    private function hasBothDimensions()
    {
        return isset($this->_props['width']) && isset($this->_props['height']);
    }

    private function shouldCrop()
    {
        if (!isset($this->_props['crop'])) {
            $this->_props['crop'] = $this->hasBothDimensions();
        }
        return $this->hasBothDimensions() && $this->_props['crop'];
    }

    private function dimensionsAreUnchanged()
    {
        if (!(isset($this->_props['width']) || isset($this->_props['height']))) {
            $this->_props['width'] = $this->_props['orig_width'];
            $this->_props['height'] = $this->_props['orig_height'];
        }
        return $this->_props['width'] == $this->_props['orig_width'] && $this->_props['height'] == $this->_props['orig_height'];
    }

    private function isCroppingSmallerImage()
    {
        if (!$this->shouldCrop()) {
            return false;
        }
        return $this->_props['width'] > $this->_props['orig_width'] || $this->_props['height'] > $this->_props['orig_height'];
    }

    private function getNewDimensionsForSmallerImage()
    {
        $width_ratio = $this->_props['orig_width'] / $this->_props['width'];
        $height_ratio = $this->_props['orig_height'] / $this->_props['height'];
        $factor = min($width_ratio, $height_ratio);
        $new_w = round($this->_props['width'] * $factor);
        $new_h = round($this->_props['height'] * $factor);
        return [$new_w, $new_h];
    }

    private function getNewDimensionsForLargerImage()
    {
        $has_width = isset($this->_props['width']);
        $has_height = isset($this->_props['height']);
        $cropping = $this->shouldCrop();
        // Get desired ratio
        $desired_ratio = $cropping ?
            $this->_props['width'] / $this->_props['height'] :
            $this->_props['orig_width'] / $this->_props['orig_height'];
        $new_w = $new_h = 0;
        if ($has_width) {
            $new_w = $this->_props['width'];
            $new_h = $has_height ? $this->_props['height'] : round($new_w / $desired_ratio);
        } elseif ($has_height) {
            $new_h = $this->_props['height'];
            $new_w = round($new_h * $desired_ratio);
        }
        if (! $cropping && $this->_props['orig_height'] <= $new_h && $this->_props['orig_width'] <= $new_w) {
            // Don't create copy of same size image when source image is too small. 
            $this->_props['width'] = $this->_props['orig_width'];
            $this->_props['height'] = $this->_props['orig_height'];
            return false;
        }
        return $cropping ?
            [$new_w, $new_h] :
            $this->constrainDimensions($new_w, $new_h);
    }


    private function constrainDimensions($width, $height)
    {
        return wp_constrain_dimensions($this->_props['orig_width'], $this->_props['orig_height'], $width, $height);
    }

    private function getNewDimensions()
    {
        if ($this->dimensionsAreUnchanged()) {
            return false;
        }
        return $this->isCroppingSmallerImage() ?
            $this->getNewDimensionsForSmallerImage() :
            $this->getNewDimensionsForLargerImage();
    }

    private function getRelativePath($path, $ext)
    {
        $rel_path = str_replace(self::getUploadDir(), '', $path);
        return substr($rel_path, 0, -1 * (strlen($ext) + 1));
    }

    private function getResizedPath($path, $width, $height, $ext)
    {
        return sprintf('%s-%sx%s.%s', $path, $width, $height, $ext);
    }

    private static function setUploadPaths()
    {
        $upload_info = wp_upload_dir();
        static::$upload_dirs[static::$upload_key] = $upload_info['basedir'];
        static::$upload_urls[static::$upload_key] = $upload_info['baseurl'];
    }

    private static function getUploadDir()
    {
        if (empty(static::$upload_dirs[static::$upload_key])) {
            static::setUploadPaths();
        }
        return static::$upload_dirs[static::$upload_key];
    }

    private static function getUploadUrl()
    {
        if (empty(static::$upload_urls[static::$upload_key])) {
            static::setUploadPaths();
        }
        return static::$upload_urls[static::$upload_key];
    }

    private function resize()
    {
        $this->generateSrcSet();
        if (! $new_dimensions = $this->getNewDimensions()) {
            return;
        }
        list($actual_w, $actual_h) = $new_dimensions;
        
        // If new size is within 1px of original size WP won't generate a new image
        if (wp_fuzzy_number_match($actual_w, $this->orig_width)) {
            $actual_w = $this->orig_width;
        }
        if (wp_fuzzy_number_match($actual_h, $this->orig_height)) {
            $actual_h = $this->orig_height;
        }
        $path = get_attached_file($this->ID);
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $rel_path = $this->getRelativePath($path, $ext);
        $resized_rel_path = $this->getResizedPath($rel_path, $actual_w, $actual_h, $ext);
        $resized_file = self::getUploadDir() . $resized_rel_path;
        if (!file_exists($resized_file)) {
            $resized = image_make_intermediate_size($path, $actual_w, $actual_h, $this->shouldCrop());
            if ($resized) {
                $actual_w = $resized['width'];
                $actual_h = $resized['height'];
            }
        }
        do_action('wp_image/register', $resized_file, $this->ID);
        $url = self::getUploadUrl() . $resized_rel_path;

        $this->_props['url'] = apply_filters('wp_get_attachment_url', $url);
        // Update width and height attributes if no crop, since one might have changed
        if (!$this->_props['crop']) {
            $this->_props['width'] = $actual_w;
            $this->_props['height'] = $actual_h;
        }
    }


    /**
     * Get post featured image
     *
     * @param null|int|WP_Post $post Post ID, WP_Post object, or empty for current post
     *
     * @return WP_Image
     */
    public static function get_featured( $post = null ) {
      $current_post = get_post( $post );
      if ( empty( $current_post ) ) {
        return null;
      }
      $attachment_id = get_post_thumbnail_id( $current_post->ID );
      if ( empty( $attachment_id ) ) {
        return null;
      }

      $image = static::get_by_attachment_id( $attachment_id );
      return $image;
    }

    /**
     * Use this factory so we can establish the validity of the data,
     * and return false if the attachment doesn't exist.
     *
     * @param $attachment_id
     *
     * @return WP_Image
     */
    public static function get_by_attachment_id( $attachment_id ) {
      $post_type = get_post_type( $attachment_id );
      if ( 'attachment' === $post_type ) {
        return new static( $attachment_id );
      }

      return false;
    }

    /**
     * Copied from media_sideload_image function, downloads image url and creates attachment post
     *
     * @param string $url
     * @param int $post_id
     *
     * @return WP_Image|WP_Error
     */
    public static function create_from_url( $url, $post_id = 0 ) {
      require_once ABSPATH . 'wp-admin/includes/image.php';
      require_once ABSPATH . 'wp-admin/includes/file.php';
      require_once ABSPATH . 'wp-admin/includes/media.php';
      preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $url, $matches );
      if ( ! $matches ) {
        return new WP_Error( 'image_sideload_failed', __( 'Invalid image URL: ' . $url ) );
      }
      $file_array = array();
      $file_array['name'] = basename( $matches[0] );
      $file_array['tmp_name'] = download_url( $url );
      if ( is_wp_error( $file_array['tmp_name'] ) ) {
        return $file_array['tmp_name'];
      }
      $id = media_handle_sideload( $file_array, $post_id );
      if ( is_wp_error( $id ) ) {
        @unlink( $file_array['tmp_name'] );
        return $id;
      }
      return self::get_by_attachment_id( $id );
    }

    public static function create_placeholder($width = 640, $height = 480, $category = 'any', $filter = '')
    {
      $image = new static(null);
      $image->placeholder = new Placeholder($width, $height, $category, $filter);
      return $image->width($width)->height($height);
    }
  }
}
