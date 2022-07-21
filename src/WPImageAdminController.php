<?php

/**
 * Class WPImageAdminController
 * @package WP_Image
 * @author  DevAnime <devanimecards@gmail.com>
 * @version 1.0
 */

namespace WP_Image;

use Imagify\Optimization\File;

class WPImageAdminController
{
    protected $meta_key = 'wp_image_sizes';
    protected $base_dir;
    protected $needs_dispatch = false;
    /**
     * @var BackgroundImageOptimizer
     */
    protected $process;

    public function __construct()
    {
        $this->process = new BackgroundImageOptimizer();
        add_action('wp_image/register', [$this, 'register'], 10, 2);
        add_action('delete_attachment', [$this, 'removeResized']);
        add_action('imagify_after_restore_media', [$this, 'handleImagifyRestore'], 10, 4);
        add_action('wp_footer', [$this, 'dispatchImageProcessing']);
        // TODO: Try invalidating during imagify attachment optimization process to cover more edge cases.
    }

    public function register($file_path, $attachment_id)
    {
        $sizes = get_post_meta($attachment_id, $this->meta_key, true) ?: [];
        if (! in_array($file_path, $sizes)) {
            $sizes[] = $file_path;
            update_post_meta($attachment_id, $this->meta_key, $sizes);
        }
        if (! (
            (defined('ENVIRONMENT') && ENVIRONMENT === 'production')
            || (defined('IMAGIFY_TEST') && IMAGIFY_TEST)
        )) {
            // Exit if not in testing mode or production environment
            return;
        }
        if (defined('IMAGIFY_VERSION') && ! file_exists($file_path . '.webp') && ! file_exists($file_path . '.webp.tmp')) {
            touch($file_path . '.webp.tmp');
            $opt_level = get_post_meta($attachment_id, '_imagify_optimization_level', true);
            $opt_level = ($opt_level === "") ? 1 : (int)$opt_level;
            $this->needs_dispatch = true;
            $this->process->push_to_queue(compact('file_path', 'opt_level'));
        }
    }

    public function removeResized($attachment_id)
    {
        $sizes = get_post_meta($attachment_id, $this->meta_key, true) ?: [];
        delete_post_meta($attachment_id, $this->meta_key);
        foreach ($sizes as $size) {
            wp_delete_file($size);
            wp_delete_file($size . '.webp');
        }
    }

    public function handleImagifyRestore($process, $response, $files, $data)
    {
        try {
            $this->removeResized($process->get_media()->get_id());
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
    }

    public function dispatchImageProcessing()
    {
        if ($this->needs_dispatch) {
            $this->process->save()->dispatch();
        }
    }
}
