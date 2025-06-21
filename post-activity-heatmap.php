<?php
/*
Plugin Name: Post Activity Heatmap
Plugin URI: https://guohao.asia/
Description: 在WordPress中显示GitHub风格的博客文章活跃度热力图
Version: 1.0.2
Author: 郭浩
Author URI: https://guohao.asia/
License: GPLv2
Text Domain: post-activity-heatmap
*/

defined('ABSPATH') || exit;

class Post_Activity_Heatmap {
    private $cache_key;
    private $days_range = 371;

    public function __construct() {
        $this->cache_key = 'pah_activity_data_' . get_current_blog_id();
        add_shortcode('post_heatmap', [$this, 'render_heatmap']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('save_post', [$this, 'clear_cache']);
        add_action('delete_post', [$this, 'clear_cache']);
        add_action('trash_post', [$this, 'clear_cache']);
        add_action('untrash_post', [$this, 'clear_cache']);
    }

    public function get_activity_data() {
        global $wpdb;
        
        $today_timestamp = current_time('timestamp');
        $end_date = date('Y-m-d', $today_timestamp);
        $start_date = date('Y-m-d', strtotime('-' . $this->days_range . ' days', $today_timestamp));
        
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
            <div class="pah-loading"><?php esc_html_e('Loading activity data...', 'post-activity-heatmap'); ?></div>
            <div class="pah-heatmap-scroll-container">
                <div class="pah-heatmap-container"></div>
            </div>
        </div>
        <?php return ob_get_clean();
    }

    public function enqueue_assets() {
        global $post;
        
        // 检查短代码是否存在
        $has_shortcode = (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'post_heatmap');
        
        // 添加过滤器允许外部控制资源加载
        $should_enqueue = apply_filters('post_activity_heatmap_enqueue', $has_shortcode);
        
        if ($should_enqueue) {
            wp_enqueue_style(
                'pah-heatmap-style',
                plugins_url('assets/css/heatmap.css', __FILE__),
                [],
                filemtime(plugin_dir_path(__FILE__) . 'assets/css/heatmap.css')
            );
            
            wp_enqueue_script(
                'pah-heatmap-script',
                plugins_url('assets/js/heatmap.js', __FILE__),
                [],
                filemtime(plugin_dir_path(__FILE__) . 'assets/js/heatmap.js'),
                true
            );
            
            $activity_data = $this->get_cached_data();
            $today_timestamp = current_time('timestamp');
            $start_date = date('Y-m-d', strtotime('-' . $this->days_range . ' days', $today_timestamp));
            
            wp_localize_script('pah-heatmap-script', 'pahData', [
                'activity' => $activity_data ?: [],
                'labels' => [
                    'today' => __('Today', 'post-activity-heatmap'),
                    'post_singular' => __('Post', 'post-activity-heatmap'),
                    'post_plural' => __('Posts', 'post-activity-heatmap')
                ],
                'startDate' => $start_date,
                'serverToday' => date('Y-m-d', $today_timestamp)
            ]);
        }
    }
}

new Post_Activity_Heatmap();

register_activation_hook(__FILE__, function() {
    $instance = new Post_Activity_Heatmap();
    $instance->get_cached_data();
});

register_deactivation_hook(__FILE__, function() {
    $instance = new Post_Activity_Heatmap();
    delete_transient($instance->cache_key);
});

if (!function_exists('display_post_activity_heatmap')) {
    function display_post_activity_heatmap() {
        echo do_shortcode('[post_heatmap]');
        
        // 确保资源加载
        do_action('post_activity_heatmap_enqueue_scripts');
    }
}

// 添加资源加载钩子
add_action('post_activity_heatmap_enqueue_scripts', function() {
    $instance = new Post_Activity_Heatmap();
    $instance->enqueue_assets();
});
