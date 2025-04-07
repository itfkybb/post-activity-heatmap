<?php
/*
Plugin Name: Post Activity Heatmap
Plugin URI: https://guohao.asia/
Description: 在WordPress中显示GitHub风格的博客文章活跃度热力图
Version: 1.0.1
Author: 郭浩
Author URI: https://guohao.asia/
License: GPLv2
Text Domain: post-activity-heatmap
*/

defined('ABSPATH') || exit;

class Post_Activity_Heatmap {
    private $cache_key = 'pah_activity_data';

    public function __construct() {
        add_shortcode('post_heatmap', [$this, 'render_heatmap']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('save_post', [$this, 'clear_cache']);
    }

    public function get_activity_data() {
        global $wpdb;
        
        $end_date = current_time('mysql');
        $start_date = date('Y-m-d', strtotime('-371 days', strtotime($end_date)));
        
        $query = $wpdb->prepare(
            "SELECT 
                DATE(post_date) AS date,
                COUNT(ID) AS count 
            FROM {$wpdb->posts}
            WHERE post_date BETWEEN %s AND %s
                AND post_type = 'post'
                AND post_status = 'publish'
            GROUP BY date
            ORDER BY date ASC",
            $start_date,
            $end_date
        );
        
        return $wpdb->get_results($query);
    }

    public function get_cached_data() {
        $data = get_transient($this->cache_key);
        if (false === $data) {
            $data = $this->get_activity_data();
            set_transient($this->cache_key, $data, 24 * HOUR_IN_SECONDS);
        }
        return $data;
    }

    public function clear_cache($post_id) {
        if (!wp_is_post_revision($post_id)) {
            delete_transient($this->cache_key);
        }
    }

    public function render_heatmap() {
    ob_start(); ?>
    <div class="pah-heatmap-wrapper">
        <!-- 新增外层包裹容器 -->
        <div class="pah-heatmap-scroll-container">
            <div class="pah-heatmap-container"></div>
        </div>
    </div>
    <?php return ob_get_clean();
}

    public function enqueue_assets() {
        global $post;
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'post_heatmap')) {
            wp_enqueue_style(
                'pah-heatmap-style',
                plugins_url('assets/css/heatmap.css', __FILE__),
                [],
                filemtime(plugin_dir_path(__FILE__).'assets/css/heatmap.css')
            );
            
            wp_enqueue_script(
                'pah-heatmap-script',
                plugins_url('assets/js/heatmap.js', __FILE__),
                [],
                filemtime(plugin_dir_path(__FILE__).'assets/js/heatmap.js'),
                true
            );
            
            $activity_data = $this->get_cached_data();
            wp_localize_script('pah-heatmap-script', 'pahData', [
                'activity' => $activity_data ?: [],
                'labels' => [
                    'today' => __('Today', 'post-activity-heatmap'),
                    'posts' => __('Posts', 'post-activity-heatmap')
                ],
                'startDate' => date('Y-m-d', strtotime('-371 days')),
                'serverToday' => date('Y-m-d', current_time('timestamp'))
            ]);
        }
    }
}

new Post_Activity_Heatmap();

register_activation_hook(__FILE__, function() {
    (new Post_Activity_Heatmap())->get_cached_data();
});

register_deactivation_hook(__FILE__, function() {
    delete_transient('pah_activity_data');
});

if (!function_exists('display_post_activity_heatmap')) {
    function display_post_activity_heatmap() {
        echo do_shortcode('[post_heatmap]');
    }
}
