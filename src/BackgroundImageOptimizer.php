<?php
/**
 * Class BackgroundImageOptimizer
 * @package WP_Image
 * @author  DevAnime <devanimecards@gmail.com>
 * @version 1.0
 */

namespace WP_Image;

use Imagify\Optimization\File;
use WP_Background_Process;

class BackgroundImageOptimizer extends WP_Background_Process
{
    protected $action = 'backstage_process_image';

    protected function task($item)
    {
        if (file_exists($item['file_path'] . '.webp')) {
            return false;
        }
        if (! class_exists('\Imagify\Optimization\File')) {
            return false;
        }
        $file = new File($item['file_path']);
        $args = [
            'backup' => false,
            'optimization_level' => $item['opt_level'],
            'convert' => 'webp',
            'keep_exif' => false,
        ];
        $file->optimize($args);
        unset($args['convert']);
        $file = new File($item['file_path']);
        $file->optimize($args);
        wp_delete_file($item['file_path'] . '.webp.tmp');

        return false;
    }
}