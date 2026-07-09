<?php
/*
 * Template Name: Revamp Upcoming Activities Template
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
        ?>
        <section id="revamppage-other-information" class="revamppage-other-information container">
            <h2 class="section-title other-information-title">
                <span class="title-cn"><?php echo esc_html($page_title); ?></span>
                <span class="title-eng"><?php echo esc_html($page_title); ?></span>
            </h2>

            <div class="information-content">
                <div class="entry-text">
                    <?php the_content(); ?>
                </div>
            </div>
        </section>
        <?php
    endwhile;
endif;

get_footer();