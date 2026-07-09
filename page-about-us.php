<?php
/* 
 * Template Name: Revamp About Template
 * Template Post Type: page
 */
if (!defined('ABSPATH')) {
    exit;
}
get_header();

if (have_posts()):
    while (have_posts()):
        the_post();
        $page_title = get_the_title(get_the_ID());

        if (empty($page_title)) {
            $page_title = get_bloginfo('name');
        }

        // Prepare hero image: featured image -> custom field -> placeholder
        $hero_img_html = '';
        if (has_post_thumbnail()) {
            $hero_img_html = get_the_post_thumbnail(get_the_ID(), 'large', array(
                'alt' => get_the_title(),
                'class' => 'about-hero-img'
            ));
        } else {
            // optional custom field 'about_hero_image' can store image URL
            $custom_img = get_post_meta(get_the_ID(), 'about_hero_image', true);
            if (!empty($custom_img)) {
                $hero_img_html = '<img src="' . esc_url($custom_img) . '" alt="' . esc_attr(get_the_title()) . '" class="about-hero-img">';
            } else {
                $placeholder = get_stylesheet_directory_uri() . '/images/placeholder-about.png';
                $hero_img_html = '<img src="' . esc_url($placeholder) . '" alt="' . esc_attr(get_the_title()) . '" class="about-hero-img">';
            }
        }
        ?>
        <section id="revamppage-about" class="revamppage-about container">
            <h2 class="section-title">
                <span class="about-cn"><?php echo esc_html($page_title); ?></span>
                <span class="about-eng"><?php echo esc_html($page_title); ?></span>
            </h2>

            <div class="about-content">
                <div class="entry-text">
                    <?php
                    // Output the page content (editable in WP editor)
                    the_content();

                    // Optional: if you want structured sub-sections you can add them here
                    ?>
                </div>
            </div>

           <!-- Lightbox overlay (hidden by default) -->
            <div id="revamppage-lightbox" class="revamppage-lightbox" aria-hidden="true" role="dialog" aria-modal="true">
                <div class="revamppage-lightbox__inner" role="document">
                    <div class="revamppage-lightbox__close-wrap" role="group" aria-label="<?php echo esc_attr__('Close controls', 'revamppage'); ?>">
                        <!-- Always include both language labels; JS will toggle visibility based on nav selection -->
                        <div class="revamppage-lightbox__close-text">
                            <span class="revamppage-lightbox__close-cn">關閉</span>
                            <span class="revamppage-lightbox__close-eng">Close</span>
                        </div>

                        <div>
                            <button type="button" class="revamppage-lightbox__close" aria-label="<?php echo esc_attr__('Close image', 'revamppage'); ?>">&times;</button>
                        </div>
                    </div>

                    <img class="revamppage-lightbox__img" src="" alt="">
                </div>
            </div>
        </section>
        <?php
    endwhile;
endif;

get_footer();