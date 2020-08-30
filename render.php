<?php

class Render
{
    static function widget($instance, $wpQuery)
    {
        $pagePermalink = get_the_permalink();

        $fontSize       = $instance['font_size'];
        $moreButtonUrl  = (!empty($instance['more_button_url']) ? $instance['more_button_url'] : '#');
        $categoryFilter = (!empty($instance['category_filter']) ? get_categories(['include' => $instance['category_filter'], 'hide_empty' => 0, 'type' => 'post']) : []);
?>
        <div class="lp-block" style="font-size: <?= $fontSize; ?>px; line-height: <?= ($fontSize + 2); ?>px;">
            <div class="lp-block-title">
                <div class="lp-block-title_area-1">
                    <h3 class="widget-title"><?= (isset($instance['title']) ? $instance['title'] : ''); ?></h3>
                </div>
                <div class="lp-block-title_area-2">
                    <?php if (isset($instance['show_more_button']) && (1 === (int)$instance['show_more_button'])) : ?>
                        <a class="lp-more-button_top-right" href="<?= $moreButtonUrl; ?>">More</a>
                    <?php endif; ?>
                </div>
            </div><!-- .lp-block-title -->
            <?php if (0 < count($categoryFilter)) : ?>
                <?php $categoryFilter = array_merge([(object)['cat_ID' => null, 'name' => '全部']], $categoryFilter); ?>
                <div class="lp-category-filter">
                    <ul class="lp-category-list">
                        <?php foreach ($categoryFilter as $value) : ?>
                            <li class="lp-category-list-item<?php if (($_GET['inc_cat'] ?? null) == $value->cat_ID) echo ' lp-category-current'; ?>">
                                <a href="<?= esc_url(add_query_arg('inc_cat', $value->cat_ID, $pagePermalink)); ?>"><?= $value->name; ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div><!-- .lp-category-filter -->
            <?php endif;
            ?>
            <div class="lp-news-loops">
                <?php if ($wpQuery->have_posts()) : ?>
                    <div class="lp-posts-block">
                        <?php if ($wpQuery->have_posts()) :
                            while ($wpQuery->have_posts()) : $wpQuery->the_post(); ?>
                                <?= self::renderPostRow($instance) ?>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <p>目前沒有可以顯示的文章。</p>
                        <?php endif; ?>
                    </div>
                    <?php
                    if (isset($instance['show_page_nav_bar']) && (1 == $instance['show_page_nav_bar']) && function_exists('wp_pagenavi')) {
                        wp_pagenavi(array('query' => $wpQuery));
                    }
                    ?>
            </div><!-- .lp-news-loops -->
        <?php endif; ?>
        <?php if (isset($instance['show_more_button']) && (2 === $instance['show_more_button'])) : ?>
            <a class="lp-more-button_bottom-right" href="<?= $moreButtonUrl; ?>">More</a>
        <?php endif; ?>
        </div><!-- .lp-block -->
    <?php
    }

    private static function renderPostRow($instance)
    {
        $postClasses = ['lp-news-row-outer'];

        // 置頂文章如在第一頁時就 highlight
        if (!is_paged() && is_sticky()) {
            $postClasses[] = 'lp-news-sticky';
        }
    ?><a class="lp-permalink" href="<?= the_permalink(); ?>">
            <div <?= post_class($postClasses); ?>>
                <div class="lp-news-row-inner">
                    <?php self::renderPostTitle($instance) ?>
                </div><!-- .lp-news-row-inner -->
            </div><!-- .lp-news-row-outer -->
        </a><!-- .lp-permalink -->
    <?php
    }

    private static function renderPostTitle($instance)
    {
        switch ($instance['display_format']) {
            case '日期/分類/標題':
                self::_time();
                self::_category();
                self::_title();
                break;

            case '日期/標題':
                self::_time();
                self::_title();
                break;

            case '標題':
                self::_title();
                break;

            default:
                self::_category();
                self::_title();
                self::_time();
                break;
        }
    }

    private static function _category()
    {
    ?>
        <div class="lp-post-category">
            <span class="lp-post-category-inner"><?= get_the_category()[0]->name; ?></span>
        </div>
    <?php
    }

    private static function _title()
    {
    ?>
        <div class="lp-post-title"><?= get_the_title() ?></div>
    <?php
    }

    private static function _time()
    {
    ?>
        <div class="lp-post-date"><?= get_the_time('Y-m-d'); ?></div>
    <?php
    }

    public static function form($widget, $instance)
    {
        $instance = array_merge(Core::DEFAULT_OPTIONS, $instance);
    ?>
        <table>
            <tr>
                <td>標題</td>
                <td>
                    <input id="<?= $widget->get_field_id('title'); ?>" name="<?= $widget->get_field_name('title'); ?>" type="text" value="<?= $instance['title']; ?>" style="width:400px;" />
                </td>
            </tr>
            <tr>
                <td>顯示筆數</td>
                <td>
                    <input id="<?= $widget->get_field_id('display_rows'); ?>" name="<?= $widget->get_field_name('display_rows'); ?>" type="text" value="<?= $instance['display_rows']; ?>" />
                </td>
            </tr>
            <tr>
                <td>字體大小</td>
                <td>
                    <input id="<?= $widget->get_field_id('font_size'); ?>" name="<?= $widget->get_field_name('font_size'); ?>" type="text" value="<?= $instance['font_size']; ?>" />px
                </td>
            </tr>
            <tr>
                <td>顯示頁碼</td>
                <td>
                    <input id="<?= $widget->get_field_id('show_page_nav_bar'); ?>" name="<?= $widget->get_field_name('show_page_nav_bar'); ?>" type="checkbox" value="1" <?= (1 == $instance['show_page_nav_bar'] ? ' checked' : ''); ?> />
                    <small style="color:#888888; margin-left:4em;">需要安裝並啟用外掛「<a target="_blank" href="//wordpress.org/plugins/wp-pagenavi/">WP PageNavi</a>」才能正常顯示。</small>
                </td>
            </tr>
            <tr>
                <td>顯示「More」連結</td>
                <td>
                    <select id="<?= $widget->get_field_id('show_more_button'); ?>" name="<?= $widget->get_field_name('show_more_button'); ?>">
                        <?php foreach (Core::DROPDOWN_OPTIONS['show_more_button'] as $key => $value) : ?>
                            <option value="<?= $key; ?>" <?= ($key == $instance['show_more_button'] ? ' selected' : ''); ?>><?= $value; ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>編輯More連結網址</td>
                <td>
                    <input id="<?= $widget->get_field_id('more_button_url'); ?>" name="<?= $widget->get_field_name('more_button_url'); ?>" type="text" value="<?= $instance['more_button_url']; ?>" style="width:400px;" />
                </td>
            </tr>
            <tr>
                <td>分類過濾</td>
                <td>
                    <?php $categories = get_categories(['hide_empty' => 0, 'type' => 'post']);
                    $field_id = $widget->get_field_id('category_filter');
                    $field_name = $widget->get_field_name('category_filter'); ?>
                    <?php foreach ($categories as $key => $value) : ?>
                        <input type="checkbox" id="<?= $field_id . '-' . $key; ?>" name="<?= $field_name; ?>[]" value="<?= $value->cat_ID; ?>" <?= (in_array($value->cat_ID, explode(',', is_string($instance['category_filter']))) ? ' checked' : ''); ?>>
                        <label for="<?= $field_id . '-' . $key; ?>"><?= $value->cat_name; ?></label>
                        <br>
                    <?php endforeach; ?>
                </td>
            </tr>
            <tr>
                <td>樣式</td>
                <td>
                    <select id="<?= $widget->get_field_id('theme'); ?>" name="<?= $widget->get_field_name('theme'); ?>">
                        <?php foreach (Core::DROPDOWN_OPTIONS['theme'] as $key => $value) : ?>
                            <option value="<?= $value; ?>" <?= ($value == $instance['theme'] ? ' selected' : ''); ?>><?= $value; ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>顯示格式</td>
                <td>
                    <select id="<?= $widget->get_field_id('display_format'); ?>" name="<?= $widget->get_field_name('display_format'); ?>">
                        <?php foreach (Core::DROPDOWN_OPTIONS['display_format'] as $key => $value) : ?>
                            <option value="<?= $value; ?>" <?= ($value == $instance['display_format'] ? ' selected' : ''); ?>><?= $value; ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>
<?php
    }
}
