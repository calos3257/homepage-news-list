<?php
/* Plugin Name: Homepage News List
 * Plugin URI: http://blog.calos.org
 * Description: Show posts as list at home page.
 * Version: 1.0 Beta
 * Author: Calos
 * Author URI: http://blog.calos.org
 */

class HomepageNewsList extends WP_Widget {
    public function __construct(){
        register_activation_hook( __FILE__, array($this, 'plugin_activated' ));
        register_deactivation_hook( __FILE__, array($this, 'plugin_deactivated' ));

        $widget_options = array(
            'classname'   => 'HomepageNewsList',
            'description' => 'Show posts as list at home page.',
        );
        $this->WP_Widget(false, 'Home Page News List', $widget_options);
    }

    public function plugin_activated(){
        // This will run when the plugin is activated, setup the database
        /*
        if( $this->networkactive ) {
            update_site_option('HomepageNewsList_enabled', 1);
        } else {
            update_option('HomepageNewsList_enabled', 1);
        }
         */
    }

    public function plugin_deactivated(){
        // This will run when the plugin is deactivated, use to delete the database
        /*
        if( $this->networkactive ) {
            update_site_option('HomepageNewsList_enabled', 0);
        } else {
            update_option('HomepageNewsList_enabled', 0);
        }
         */
    }

    // Set default value in here.
    protected function instance_filter( $instance ) {
        $instance_default = [
            'display_rows'       => 5,
            'display_PageNavBar' => 0,
            'MoreButtonUrl'      => '',
            'font_size'          => 14,
            'category_filter'    => '',
        ];

        foreach ( $instance_default as $key => $value ) {
            if ( false === isset( $instance[$key] ) )
                $instance[$key] = $value;
        }

        return $instance;
    }

    public function widget($args, $instance) {
        wp_enqueue_style('hnl-stylesheet', plugins_url('style.css', __FILE__), array());

        $instance = $this->instance_filter($instance);
        $page_permalink = get_the_permalink();

        $display_rows    = $instance['display_rows'];
        $MoreButtonUrl   = $instance['MoreButtonUrl'];
        $font_size       = $instance['font_size'];
        $category_filter = ( !empty($instance['category_filter']) ? get_categories(['include' => $instance['category_filter']]) : [] );

        global $wp_query;
        $paged = (int)get_query_var('page') + (int)get_query_var('paged');
        if ( 0 >= $paged ) $paged = 1;
        $tmp_wp_query = $wp_query;
        $query_vars = [
            'posts_per_page'   => $display_rows,
            'offset'           => $display_rows * ($paged - 1),
            'paged'            => $paged,
            'orderby'          => 'post_date',
            'order'            => 'DESC',
            'post_type'        => 'post',
            'post_status'      => 'publish',
        ];

        if ( isset($_GET['inc_cat']) ) {
            $query_vars['cat'] = $_GET['inc_cat'];
        } else {
            $_GET['inc_cat'] = null;
        }

        $wp_query = new WP_Query($query_vars);
        ?>
        <div class="hnl_block" style="font-size: <?= $font_size; ?>px; line-height: <?= ($font_size + 2); ?>px;">
            <div class="hnl_block-title">
                <div class="hnl_block-title_area-1">
                    <h3 class="widget-title"><?= ( isset($instance['title']) ? $instance['title'] : '' ); ?></h3>
                </div>
                <div class="hnl_block-title_area-2">
                <?php if ( isset($instance['display_MoreButton']) && ( 1 === $instance['display_MoreButton'] ) ): ?>
                    <a class="hnl_more-button_top-right" href="<?= $MoreButtonUrl; ?>">More</a>
                <?php endif; ?>
                </div>
            </div><!-- .hnl_block-title -->
            <?php if ( 0 < count($category_filter) ): ?>
            <?php $category_filter = array_merge([ (object)['cat_ID' => null, 'name' => '全部'] ], $category_filter); ?>
            <div class="hnl_category-filter">
                <ul class="hnl_category-list">
                    <li class="hnl_category-list-item">分類：</a>
                <?php foreach ( $category_filter as $key => $value ): ?>
                    <li class="hnl_category-list-item<?php if ( $_GET['inc_cat'] == $value->cat_ID ) echo ' hnl_category-current'; ?>"><a href="<?php
                        echo esc_url( add_query_arg('inc_cat', $value->cat_ID, $page_permalink) );
                    ?>"><?= $value->name; ?></a></li>
                <?php endforeach; ?>
                </ul>
            </div><!-- .hnl_category-filter -->
            <?php endif; #40 ?>
            <div class="hnl_news-loops">
            <?php if ( 0 < count($wp_query) ): ?>
                <div>
                <?php if ( $wp_query->have_posts() ):
                    while ( $wp_query->have_posts() ): $wp_query->the_post(); ?>
                        <a class="hnl_permalink" href="<?= the_permalink(); ?>">
                            <div <?= post_class('hnl_news-row-outer'); ?>>
                                <div class="hnl_news-row-inner">
                                    <div class="hnl_post-category">[<?= get_the_category()[0]->name; ?>]</div>
                                    <div class="hnl_post-title">
                                        <?php if ( (null == $_GET['inc_cat']) && (1 === $paged) && is_sticky() ) echo '【置頂】'; ?>
                                        <?= the_title(); ?>
                                    </div>
                                    <div class="hnl_post-date"><?= the_time('Y-m-d'); ?></div>
                                </div><!-- .hnl_news-row-inner -->
                            </div><!-- .hnl_news-row-outer -->
                        </a><!-- .hnl_permalink -->
                    <?php endwhile; ?>
                <?php else: ?>
                    Not have exists posts.
                <?php endif; ?>
                </div>
                <?php
                    if ( isset($instance['display_PageNavBar']) && ( 1 == $instance['display_PageNavBar'] ) && function_exists( 'wp_pagenavi' ) ) {
                        wp_pagenavi( array('query' => $wp_query) );
                    }
                ?>
            </div><!-- .hnl_news-loops -->
            <?php endif; ?>
            <?php if ( isset($instance['display_MoreButton']) && ( 2 === $instance['display_MoreButton'] ) ): ?>
                <a class="hnl_more-button_bottom-right" href="<?= $MoreButtonUrl; ?>">More</a>
            <?php endif; ?>
        </div><!-- .hnl_block --><?php

        $wp_query = $tmp_wp_query;

        return true;
    }

    function form($instance) { ?>
        <table>
            <tr>
                <td>標題</td>
                <td>
                    <input id="<?= $this->get_field_id('title'); ?>"
                        name="<?= $this->get_field_name('title'); ?>"
                        type="text"
                        value="<?= $instance['title']; ?>" />
                </td>
            </tr>
            <tr>
                <td>顯示筆數</td>
                <td>
                    <input id="<?= $this->get_field_id('display_rows'); ?>"
                        name="<?= $this->get_field_name('display_rows'); ?>"
                        type="text"
                        value="<?= $instance['display_rows']; ?>" />
                </td>
            </tr>
            <tr>
                <td>字體大小</td>
                <td>
                    <input id="<?= $this->get_field_id('font_size'); ?>"
                        name="<?= $this->get_field_name('font_size'); ?>"
                        type="text"
                        value="<?= $instance['font_size']; ?>" />px
                </td>
            </tr>
            <tr>
                <td>顯示頁碼</td>
                <td>
                    <input id="<?= $this->get_field_id('display_PageNavBar'); ?>"
                        name="<?= $this->get_field_name('display_PageNavBar'); ?>"
                        type="checkbox"
                        value="1"
                        <?= ( 1 == $instance['display_PageNavBar'] ? ' checked' : '' ); ?> />
                </td>
            </tr>
            <tr>
                <td>顯示「More」連結</td>
                <td>
                    <select id="<?= $this->get_field_id('display_MoreButton'); ?>" name="<?= $this->get_field_name('display_MoreButton'); ?>">
                    <?php $item_options = ['不顯示', '顯示於區塊右上角', '顯示於區塊右下角']; foreach ( $item_options as $key => $value ): ?>
                        <option value="<?= $key; ?>"<?= ( $key == $instance['display_MoreButton'] ? ' selected' : '' ); ?>><?= $value; ?></option>
                    <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>編輯More連結網址</td>
                <td>
                    <input id="<?= $this->get_field_id('MoreButtonUrl'); ?>"
                        name="<?= $this->get_field_name('MoreButtonUrl'); ?>"
                        type="text"
                        value="<?= $instance['MoreButtonUrl']; ?>"
                        style="width:360px;" />
                </td>
            </tr>
            <tr>
                <td>分類過濾</td>
                <td>
                <?php $categories = get_categories(); $field_id = $this->get_field_id('category_filter'); $field_name = $this->get_field_name('category_filter'); ?>
                <?php foreach ( $categories as $key => $value ): ?>
                    <input type="checkbox"
                           id="<?= $field_id . '-' . $key; ?>"
                           name="<?= $field_name; ?>[]"
                           value="<?= $value->cat_ID; ?>"
                           <?= ( in_array($value->cat_ID, explode(',', $instance['category_filter'])) ? ' checked' : '' ); ?>>
                    <label for="<?= $field_id . '-' . $key; ?>"><?= $value->cat_name; ?></label>
                    <br>
                <?php endforeach; ?>
                </td>
            </tr>
        </table>
        <?php
    }

    function update($new_instance, $old_instance) {
        $instance = array();
        if ( ! empty( $new_instance['title'] ) ) {
            $instance['title'] = strip_tags( stripslashes($new_instance['title']) );
        }

        if ( ! empty( $new_instance['display_rows'] ) ) {
            $instance['display_rows'] = (int) $new_instance['display_rows'];
            if ( 0 > $instance['display_rows'] )
                $instance['display_rows'] = 5;
        }

        if ( ! empty( $new_instance['font_size'] ) ) {
            $instance['font_size'] = (int) $new_instance['font_size'];
            if ( 0 > $instance['font_size'] )
                $instance['font_size'] = 14;
        }

        if ( ! empty( $new_instance['display_MoreButton'] ) ) {
            $instance['display_MoreButton'] = (int) $new_instance['display_MoreButton'];
        }

        if ( ! empty( $new_instance['display_PageNavBar'] ) ) {
            $instance['display_PageNavBar'] = (int) $new_instance['display_PageNavBar'];
        }

        if ( ! empty( $new_instance['MoreButtonUrl'] ) ) {
            $instance['MoreButtonUrl'] = strip_tags( stripslashes($new_instance['MoreButtonUrl']) );
        }

        if ( ! empty( $new_instance['category_filter'] ) ) {
            $instance['category_filter'] = implode(',', $new_instance['category_filter']);
        }

        return $instance;
    }
}

function HomepageNewsList() {
    // 註冊小工具
    register_widget('HomepageNewsList');
}

// 在小工具初始化的時候執行HomepageNewsList function.
add_action('widgets_init', 'HomepageNewsList');