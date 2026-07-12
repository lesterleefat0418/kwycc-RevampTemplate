<?php
/*
 * Template Name: Smartteen 透視
 * Template Post Type: page
 */
if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (have_posts()):
    while (have_posts()): the_post();
        $page_title = get_the_title(get_the_ID());
        if (empty($page_title)) {
            $page_title = get_bloginfo('name');
        }
        ?>
        <section id="revamppage-smartteen" class="revamppage-smartteen">
            <h2 class="section-title">
                <span class="smartteen-cn"><?php echo esc_html($page_title); ?></span>
                <span class="smartteen-eng"><?php echo esc_html($page_title); ?></span>
            </h2>

            <div class="smartteen-carousel-section">
                <?php
                $smartteen_query = new WP_Query(array(
                    'post_type' => 'smartteen',
                    'posts_per_page' => -1,
                    'post_status' => 'publish',
                    'orderby' => 'date',
                    'order' => 'DESC',
                ));
                if ($smartteen_query->have_posts()):
                    ?>
                    <div class="smartteen-carousel-wrapper">
                        <button type="button" class="smartteen-carousel__nav smartteen-carousel__prev" aria-label="Previous book">‹</button>
                        <div class="smartteen-carousel-viewport">
                            <div class="smartteen-carousel" id="smartteenCarousel" tabindex="0" role="list">
                                <?php
                                $index = 0;
                                while ($smartteen_query->have_posts()): $smartteen_query->the_post();
                                $book_id = get_the_ID();
                                $book_title = get_the_title();
                                // Use the theme/site thumbnail size for previews - filterable
                                // For the carousel we use the original uploaded image (no cropping) to preserve aspect ratio.
                                $cover_html = '';
                                $thumb_id = get_post_meta($book_id, '_smartteen_thumb_id', true);
                                if ($thumb_id && get_post_status($thumb_id)) {
                                    // use full size to avoid WP crop artifacts; CSS will constrain display
                                    $cover_html = wp_get_attachment_image($thumb_id, 'full', false, array('alt' => $book_title));
                                } elseif (has_post_thumbnail($book_id)) {
                                    $cover_html = wp_get_attachment_image(get_post_thumbnail_id($book_id), 'full', false, array('alt' => $book_title));
                                } else {
                                    $placeholder = get_stylesheet_directory_uri() . '/images/placeholder-book.png';
                                    $cover_html = '<img src="' . esc_url($placeholder) . '" alt="' . esc_attr($book_title) . '">';
                                }

                                $intro = get_post_meta($book_id, '_smartteen_intro', true);
                                if (empty($intro)) {
                                    $intro = get_the_excerpt();
                                }
                                $pages = get_post_meta($book_id, '_smartteen_pages', true);
                                if (!is_array($pages)) {
                                    $pages = array();
                                }
                                $book_data = array(
                                    'id' => $book_id,
                                    'title' => $book_title,
                                    'cover_html' => wp_kses_post($cover_html),
                                    'intro' => wp_kses_post($intro),
                                    'pages' => array_values($pages),
                                );
                                ?>
                                <div class="smartteen-card" data-index="<?php echo esc_attr($index); ?>" data-book="<?php echo esc_attr(wp_json_encode($book_data)); ?>" role="button" tabindex="0">
                                    <?php echo $cover_html; ?>
                                </div>
                                <?php
                                $index++;
                            endwhile;
                            wp_reset_postdata();
                            ?>
                            </div>
                        </div>
                        <button type="button" class="smartteen-carousel__nav smartteen-carousel__next" aria-label="Next book">›</button>
                    </div>
                    <div class="smartteen-carousel-footer">
                        <div id="smartteenActiveInfo" class="smartteen-active-info">
                            <h3 id="smartteenActiveTitle" class="smartteen-active-title"></h3>
                            <div id="smartteenActiveIntro" class="smartteen-active-intro"></div>
                        </div>
                    </div>
                    <?php
                else:
                    ?>
                    <div class="smartteen-empty-state">
                        <p><?php esc_html_e('No Smartteen books have been published yet. Please add items in the Smartteen Books admin section.', 'revamppage'); ?></p>
                    </div>
                    <?php
                endif;
                ?>
            </div>
        </section>

        <div id="smartteenOverlay" class="smartteen-overlay" aria-hidden="true" role="dialog" aria-modal="true">
            <div class="smartteen-overlay__inner" role="document">
                <div class="smartteen-overlay__close-wrap" role="group" aria-label="<?php echo esc_attr__('Close Smartteen overlay', 'revamppage'); ?>">
                    <div class="smartteen-overlay__close-text">
                        <span class="smartteen-overlay__close-cn">關閉</span>
                        <span class="smartteen-overlay__close-eng">Close</span>
                    </div>
                    <button type="button" class="smartteen-overlay__close" aria-label="<?php echo esc_attr__('Close overlay', 'revamppage'); ?>">×</button>
                </div>

                <div class="smartteen-overlay__content">
                    <div class="smartteen-overlay__book-meta">
                        <div class="smartteen-overlay__book-cover">
                            <img src="" alt="">
                        </div>
                        <div class="smartteen-overlay__book-info">
                            <h3 class="smartteen-overlay__book-title"></h3>
                            <div class="smartteen-overlay__book-intro"></div>
                        </div>
                    </div>
                    <div class="smartteen-overlay__page-controls">
                        <button type="button" class="smartteen-overlay__page-nav smartteen-overlay__page-prev" aria-label="Previous page">‹</button>
                        <div class="smartteen-overlay__page-indicator"></div>
                        <button type="button" class="smartteen-overlay__page-nav smartteen-overlay__page-next" aria-label="Next page">›</button>
                    </div>
                    <div class="smartteen-overlay__page-viewer">
                        <div class="smartteen-overlay__book-page" aria-live="polite"></div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    endwhile;
endif;

get_footer();
