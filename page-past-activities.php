<?php
/*
 * Template Name: Revamp Past Activities Template
 * Template Post Type: page
 */
if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (have_posts()):
    while (have_posts()):
        the_post();

        $page_title_cn = get_the_title(get_the_ID());
        if (empty($page_title_cn)) {
            $page_title_cn = get_bloginfo('name');
        }

        $page_title_en = 'Past Activities';
        $current_page_id = get_the_ID();
        $page_permalink = get_permalink($current_page_id);
        $paged = max(1, get_query_var('paged') ? get_query_var('paged') : get_query_var('page'));
        $posts_per_page = 8;
        $filter_values = array(
            'cat' => isset($_GET['cat']) ? sanitize_text_field(wp_unslash($_GET['cat'])) : '',
            'year' => isset($_GET['year']) ? sanitize_text_field(wp_unslash($_GET['year'])) : '',
            'month' => isset($_GET['month']) ? sanitize_text_field(wp_unslash($_GET['month'])) : '',
            's' => isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '',
            'lang' => isset($_GET['lang']) ? sanitize_text_field(wp_unslash($_GET['lang'])) : '',
        );
        $has_filters = !empty($filter_values['cat']) || !empty($filter_values['year']) || !empty($filter_values['month']) || !empty($filter_values['s']);

        ?>
        <section id="revamppage-past-activities" class="revamppage-past-activities"
            data-cn-title="<?php echo esc_attr($page_title_cn); ?>"
            data-en-title="<?php echo esc_attr($page_title_en); ?>">
            <h2 class="section-title">
                <span class="title-cn"><?php echo esc_html($page_title_cn); ?></span>
                <span class="title-eng"><?php echo esc_html($page_title_en); ?></span>
            </h2>

            <div class="pa-body">
                <div class="pa-activities">
                    <div class="pa-controls">
                        <form id="pa-filter-form" method="get" class="pa-filter-form" role="search" aria-label="<?php esc_attr_e('Filter posts', 'revamppage'); ?>">
                        <input type="hidden" name="paged" value="<?php echo (int) $paged; ?>">
                        <input type="hidden" name="page_id" value="<?php echo esc_attr(absint($current_page_id)); ?>">
                        <input type="hidden" name="lang" value="<?php echo esc_attr(revamppage_get_current_language_code($filter_values['lang'])); ?>">

                            <select id="pa-cat" name="cat" class="pa-select">
                                <option value="" data-cn="類別" data-en="All Categories">類別</option>
                                <?php
                                $cats = get_terms(array_merge(
                                        revamppage_get_language_query_args($filter_values['lang']),
                                        array(
                                            'taxonomy' => 'category',
                                            'hide_empty' => false,
                                        )
                                    ));

                                if ($cats && !is_wp_error($cats)) {
                                    foreach ($cats as $c) {
                                        printf(
                                            '<option value="%d" data-cn="%s" data-en="%s"%s>%s</option>',
                                            intval($c->term_id),
                                            esc_attr($c->name),
                                            esc_attr($c->name),
                                            selected(isset($_GET['cat']) ? intval($_GET['cat']) : 0, intval($c->term_id), false),
                                            esc_html($c->name)
                                        );
                                    }
                                }
                                ?>
                            </select>

                            <select id="pa-year" name="year" class="pa-select">
                                <option value="" data-cn="年份" data-en="Year">年份</option>
                                <?php
                                global $wpdb;
                                $years = $wpdb->get_col("SELECT DISTINCT YEAR(post_date) FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status = 'publish' AND YEAR(post_date) >= 2020 ORDER BY 1 DESC");
                                if ($years) {
                                    foreach ($years as $y) {
                                        printf(
                                            '<option value="%d"%s>%d</option>',
                                            intval($y),
                                            selected(isset($_GET['year']) ? intval($_GET['year']) : 0, intval($y), false),
                                            intval($y)
                                        );
                                    }
                                }
                                ?>
                            </select>

                            <select id="pa-month" name="month" class="pa-select">
                                <option value="" data-cn="月份" data-en="Month">月份</option>
                                <?php
                                for ($m = 1; $m <= 12; $m++) {
                                    printf(
                                        '<option value="%02d"%s>%s</option>',
                                        $m,
                                        selected(isset($_GET['month']) ? intval($_GET['month']) : 0, $m, false),
                                        date_i18n('F', mktime(0, 0, 0, $m, 1))
                                    );
                                }
                                ?>
                            </select>

                            <input
                                id="pa-s"
                                name="s"
                                type="search"
                                class="pa-search"
                                placeholder="搜尋活動"
                                data-cn-placeholder="搜尋活動"
                                data-en-placeholder="Search Activities"
                                value="<?php echo isset($_GET['s']) ? esc_attr(sanitize_text_field(wp_unslash($_GET['s']))) : ''; ?>"
                            >

                            <button type="submit" class="pa-btn"></button>
                        </form>

                        <div class="pa-tag-list" aria-hidden="false">
                            <?php
                                $top_cats = get_terms(array_merge(
                                        revamppage_get_language_query_args($filter_values['lang']),
                                        array(
                                            'taxonomy' => 'category',
                                            'hide_empty' => true,
                                        )
                                    ));

                            if ($top_cats && !is_wp_error($top_cats)) {
                                foreach ($top_cats as $tc) {
                                    $tag_link = add_query_arg(
                                        array(
                                            'page_id' => absint($current_page_id),
                                            'cat' => intval($tc->term_id),
                                            'lang' => revamppage_get_current_language_code($filter_values['lang']),
                                        ),
                                        $page_permalink
                                    );

                                    printf(
                                        '<a class="pa-tag" href="%s"><span class="pa-tag-cn">%s</span><span class="pa-tag-en" style="display:none;">%s</span></a>',
                                        esc_url($tag_link),
                                        esc_html($tc->name),
                                        esc_html($tc->name)
                                    );
                                }
                            }
                            ?>
                        </div>
                    </div>

                    <div class="pa-all-activities">
                        <?php the_content(); ?>
                    </div>

                    <?php
                    $pa_query_args = revamppage_build_past_activities_query_args($current_page_id, $paged, $posts_per_page, $filter_values);
                    $pa_query = new WP_Query($pa_query_args);
                    $pa_render = revamppage_render_past_activities_markup($pa_query, $paged, $current_page_id, $filter_values);
                    ?>

                    <div class="pa-grid" id="pa-grid" aria-live="polite">
                        <?php echo $pa_render['grid_html']; ?>
                    </div>

                    <?php if ($pa_render['found_posts'] > 0): ?>
                        <?php echo $pa_render['pagination_html']; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <?php
    endwhile;
endif;

get_footer();