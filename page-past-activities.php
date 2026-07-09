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
        $paged = max(1, get_query_var('paged') ? get_query_var('paged') : get_query_var('page'));
        $posts_per_page = 8;
        $has_filters = !empty($_GET['cat']) || !empty($_GET['year']) || !empty($_GET['month']) || !empty($_GET['s']);

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

                            <select id="pa-cat" name="cat" class="pa-select">
                                <option value="" data-cn="類別" data-en="All Categories">類別</option>
                                <?php
                                $cats = get_terms(array(
                                    'taxonomy' => 'category',
                                    'hide_empty' => false,
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
                                // Only years from 2020 onwards
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
                                value="<?php echo isset($_GET['s']) ? esc_attr(sanitize_text_field($_GET['s'])) : ''; ?>"
                            >

                            <button type="submit" class="pa-btn"></button>
                        </form>

                        <div class="pa-tag-list" aria-hidden="false">
                            <?php
                            // Show all non-empty categories (remove numeric limit)
                            $top_cats = get_terms(array(
                                'taxonomy' => 'category',
                                'hide_empty' => true,
                            ));

                            if ($top_cats && !is_wp_error($top_cats)) {
                                foreach ($top_cats as $tc) {
                                    printf(
                                        '<a class="pa-tag" href="%s"><span class="pa-tag-cn">%s</span><span class="pa-tag-en" style="display:none;">%s</span></a>',
                                        esc_url(add_query_arg('cat', intval($tc->term_id), get_permalink())),
                                        esc_html($tc->name),
                                        esc_html($tc->name)
                                    );
                                }
                            }
                            ?>
                        </div>
                    </div>

                    <div class="pa-all-activities">
                        <?php
                        the_content();
                        ?>
                    </div>

                    <div class="pa-grid" id="pa-grid" aria-live="polite">
                        <?php
                        $args = array(
                            'post_type' => 'post',
                            'post_status' => 'publish',
                            'posts_per_page' => $posts_per_page,
                            'paged' => $paged,
                            'orderby' => 'date',
                            'order' => 'DESC',
                            // Only posts from 2020-01-01 and later
                            'date_query' => array(
                                array(
                                    'after'     => array(
                                        'year'  => 2020,
                                        'month' => 1,
                                        'day'   => 1,
                                    ),
                                    'inclusive' => true,
                                ),
                            ),
                        );

                        if ($has_filters) {
                            if (!empty($_GET['cat'])) {
                                $args['cat'] = intval($_GET['cat']);
                            }

                            if (!empty($_GET['year'])) {
                                $args['year'] = intval($_GET['year']);
                            }

                            if (!empty($_GET['month'])) {
                                $args['monthnum'] = intval($_GET['month']);
                            }

                            if (!empty($_GET['s'])) {
                                $args['s'] = sanitize_text_field($_GET['s']);
                            }
                        }

                        $pa_query = new WP_Query($args);

                        if ($pa_query->have_posts()):
                            while ($pa_query->have_posts()):
                                $pa_query->the_post();

                                $post_categories = get_the_category();
                                $category_name = '';
                                if (!empty($post_categories)) {
                                    $category_name = $post_categories[0]->name;
                                }

                                ?>
                                <article class="pa-card" id="post-<?php the_ID(); ?>">
                                    <a class="pa-card-link" href="<?php the_permalink(); ?>">
                                        <div class="pa-card-thumb">
                                            <?php echo wp_kses_post(revamppage_get_activity_image_html(get_the_ID(), 'medium')); ?>
                                        </div>
                                        <div class="pa-card-body">
                                            <?php if (!empty($category_name)): ?>
                                                <!--<div class="pa-card-cat">
                                                    <span class="pa-card-cat-cn"><?php echo esc_html($category_name); ?></span>
                                                    <span class="pa-card-cat-en" style="display:none;"><?php echo esc_html($category_name); ?></span>
                                                </div>-->
                                            <?php endif; ?>

                                            <h3 class="pa-card-title">
                                                <span class="pa-title-cn"><?php echo esc_html(get_the_title()); ?></span>
                                                <span class="pa-title-en" style="display:none;"><?php echo esc_html(get_the_title()); ?></span>
                                            </h3>

                                            <div class="pa-card-meta">
                                                <?php
                                                // Chinese: "日期: DD/MM/YYYY"
                                                $raw_date = get_the_date('Y-m-d');
                                                $timestamp = strtotime($raw_date);
                                                $date_cn = $timestamp ? date_i18n('d/m/Y', $timestamp) : esc_html(get_the_date());
                                                $date_en = esc_html(get_the_date('F j, Y'));
                                                ?>
                                                <span class="pa-date-cn">日期: <?php echo esc_html($date_cn); ?></span>
                                                <span class="pa-date-en" style="display:none;">Date: <?php echo $date_en; ?></span>
                                            </div>
                                            <!--<div class="pa-card-excerpt"><?php echo wp_trim_words(get_the_excerpt() ? get_the_excerpt() : get_the_content(), 18, '...'); ?></div>-->
                                        </div>
                                    </a>
                                </article>
                                <?php
                            endwhile;
                            wp_reset_postdata();
                        else:
                            ?>
                            <div class="pa-empty">
                                <p><?php esc_html_e('No posts found. Please try another filter.', 'revamppage'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                                <?php if ($pa_query->found_posts > 0): ?>
                <div class="pa-pagination">
                    <div class="pa-pagination-info"
                        data-cn="共 <?php echo (int) $pa_query->max_num_pages; ?> 頁・目前第 <?php echo (int) $paged; ?> 頁"
                        data-en="Page <?php echo (int) $paged; ?> of <?php echo (int) $pa_query->max_num_pages; ?>">
                        <?php echo esc_html(sprintf(__('共 %d 頁・目前第 %d 頁', 'revamppage'), (int) $pa_query->max_num_pages, (int) $paged)); ?>
                    </div>

                    <?php
                    $pagination_query_args = array();

                    if (!empty($_GET['cat'])) {
                        $pagination_query_args['cat'] = intval($_GET['cat']);
                    }

                    if (!empty($_GET['year'])) {
                        $pagination_query_args['year'] = intval($_GET['year']);
                    }

                    if (!empty($_GET['month'])) {
                        $pagination_query_args['month'] = intval($_GET['month']);
                    }

                    if (!empty($_GET['s'])) {
                        $pagination_query_args['s'] = sanitize_text_field($_GET['s']);
                    }

                    $pagination_base = add_query_arg($pagination_query_args, get_permalink());
                    $pagination_base = add_query_arg('paged', '%#%', $pagination_base);

                    echo paginate_links(array(
                        'base' => $pagination_base,
                        'format' => '',
                        'current' => $paged,
                        'total' => (int) $pa_query->max_num_pages,
                        'prev_text' => '«',
                        'next_text' => '»',
                        'mid_size' => 2,
                        'end_size' => 1,
                    ));
                    ?>
                </div>
            <?php endif; ?>

                </div>
            </div>
        </section>
        <?php
    endwhile;
endif;

get_footer();