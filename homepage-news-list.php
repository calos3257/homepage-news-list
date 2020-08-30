<?php
/* Plugin Name: Homepage News List
 * Plugin URI: https://github.com/caloskao/homepage-news-list
 * Description: Show posts as list.
 * Version: 1.0.0-beta.2
 * Author: Calos
 * Author URI: http://caloskao.org
 */

require_once __DIR__ . '/render.php';

class HomepageNewsList extends WP_Widget
{
    const WIDGET_NAME = 'Home Page News List';

    const WIDGET_SLUG = 'home-page-news-list';

    const WIDGET_OPTIONS = [
        'classname'   => 'HomepageNewsList',
        'description' => 'Show posts as list.',
    ];

    const DROPDOWN_OPTIONS = [
        'show_more_button' => ['不顯示', '顯示於區塊右上角', '顯示於區塊右下角'],
        'theme' => ['Default', 'Block'],
        'display_format' => ['分類/標題/日期', '日期/分類/標題', '日期/標題', '標題'],
    ];

    const DEFAULT_OPTIONS = [
        'title'             => '',
        'display_rows'      => 5,
        'show_page_nav_bar' => 0,
        'show_more_button'  => 0,
        'more_button_url'   => '',
        'font_size'         => 14,
        'category_filter'   => '',
        'theme'             => 'default',
        'display_format'    => 0,
    ];

    public function __construct()
    {
        register_activation_hook(__FILE__, array($this, 'plugin_activated'));
        register_deactivation_hook(__FILE__, array($this, 'plugin_deactivated'));


        parent::__construct(self::WIDGET_SLUG, self::WIDGET_NAME, self::WIDGET_OPTIONS);
    }

    public function plugin_activated()
    {
        // This will run when the plugin is activated, setup the database
        /*
        if( $this->networkactive ) {
            update_site_option('HomepageNewsList_enabled', 1);
        } else {
            update_option('HomepageNewsList_enabled', 1);
        }
         */
    }

    public function plugin_deactivated()
    {
        // This will run when the plugin is deactivated, use to delete the database
        /*
        if( $this->networkactive ) {
            update_site_option('HomepageNewsList_enabled', 0);
        } else {
            update_option('HomepageNewsList_enabled', 0);
        }
         */
    }

    // Fill default values
    protected function instance_filter($instance)
    {
        return array_merge(self::DEFAULT_OPTIONS, $instance);
    }

    public function widget($args, $instance)
    {
        $instance['theme'] = strtolower($instance['theme']);
        $stylesheetPath = plugin_dir_path(__FILE__) . '/css/' . $instance['theme'] . '.css';
        $stylesheetUrl  = plugins_url('css/default.css', __FILE__);
        if (file_exists($stylesheetPath)) {
            $stylesheetUrl = plugins_url('css/' . $instance['theme'] . '.css', __FILE__);
        }
        wp_enqueue_style('hnl-stylesheet', $stylesheetUrl, array());
        // wp_enqueue_style('hnl-twbs', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css', []);

        $instance = $this->instance_filter($instance);

        $displayRows = $instance['display_rows'];

        global $wpQuery;

        $paged = (int)get_query_var('page') + (int)get_query_var('paged');

        if (0 >= $paged) $paged = 1;

        $tmpWpQuery = $wpQuery;

        $queryVars = [
            'cat'                 => $_GET['inc_cat'] ?? null,
            'ignore_sticky_posts' => 0,
            'posts_per_page'      => $displayRows,
            'paged'               => $paged,
            'post_type'           => 'post',
            'post_status'         => 'publish',
        ];

        $wpQuery = new WP_Query($queryVars);

        HNLRender::widget($instance, $wpQuery);

        $wpQuery = $tmpWpQuery;

        return true;
    }

    function form($instance)
    {
        HNLRender::form($this, $instance);
    }

    function update($newInstance, $oldInstance)
    {
        $instance = array_merge(self::DEFAULT_OPTIONS, $newInstance);
        $instance['more_button_url'] = strip_tags(stripslashes($instance['more_button_url']));
        $instance['category_filter'] = implode(',', $instance['category_filter']);

        return $instance;
    }
}

function HomepageNewsList()
{
    // 註冊小工具
    register_widget('HomepageNewsList');
}

// 在小工具初始化的時候執行HomepageNewsList function.
add_action('widgets_init', 'HomepageNewsList');
