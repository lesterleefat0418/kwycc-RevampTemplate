<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()):
    the_post();

    $page_title_cn = get_the_title(get_the_ID());
    if (empty($page_title_cn)) {
        $page_title_cn = get_bloginfo('name');
    }

    $page_title_en = '';
    $post_id = get_the_ID();

    // 1) Try to get images attached to this post (preferred)
    $attachments = get_posts(array(
        'post_type'      => 'attachment',
        'post_mime_type' => 'image',
        'posts_per_page' => -1,
        'post_parent'    => $post_id,
        'orderby'        => 'menu_order ID',
        'order'          => 'ASC',
    ));

    $gallery_items = array();

    if (!empty($attachments)) {
        // Use attached images
        foreach ($attachments as $att) {
            $gallery_items[] = array(
                'type' => 'attachment',
                'id'   => $att->ID,
            );
        }
    } else {
        // 2) Gallery shortcode images (returns URLs)
        $gallery_urls = get_post_gallery_images($post_id);
        if (!empty($gallery_urls)) {
            foreach ($gallery_urls as $url) {
                $gallery_items[] = array(
                    'type' => 'url',
                    'url'  => $url,
                );
            }
        }

        // 3) Parse block content for core/gallery or core/image blocks (Gutenberg)
        if (empty($gallery_items)) {
            $content = get_post_field('post_content', $post_id, 'raw');
            if (!empty($content) && function_exists('parse_blocks')) {
                $blocks = parse_blocks($content);
                $collect_from_blocks = function ($blocks) use (&$collect_from_blocks, &$gallery_items) {
                    foreach ($blocks as $b) {
                        if (!empty($b['blockName'])) {
                            if ($b['blockName'] === 'core/image') {
                                $attrs = $b['attrs'] ?? array();
                                if (!empty($attrs['id'])) {
                                    $gallery_items[] = array('type' => 'attachment', 'id' => intval($attrs['id']));
                                } elseif (!empty($attrs['url'])) {
                                    $gallery_items[] = array('type' => 'url', 'url' => $attrs['url']);
                                }
                            } elseif ($b['blockName'] === 'core/gallery') {
                                $attrs = $b['attrs'] ?? array();
                                if (!empty($attrs['ids']) && is_array($attrs['ids'])) {
                                    foreach ($attrs['ids'] as $id) {
                                        $gallery_items[] = array('type' => 'attachment', 'id' => intval($id));
                                    }
                                }
                                // some galleries may include images in innerBlocks
                            }
                        }
                        if (!empty($b['innerBlocks'])) {
                            $collect_from_blocks($b['innerBlocks']);
                        }
                    }
                };
                $collect_from_blocks($blocks);
            }
        }

        // 4) Fallback: find <img> src attributes in post content (covers many editors / pasted images)
        if (empty($gallery_items)) {
            $content = isset($content) ? $content : get_post_field('post_content', $post_id, 'raw');
            if (!empty($content)) {
                if (preg_match_all('/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $content, $matches)) {
                    $urls = array_unique($matches[1]);
                    foreach ($urls as $u) {
                        $gallery_items[] = array('type' => 'url', 'url' => $u);
                    }
                }
            }
        }
    }

    // debug (optional): shows detection in page source / console
    echo '<!-- gallery-detect: post=' . (int) $post_id . ' items=' . count($gallery_items) . ' -->' . PHP_EOL;
    echo '<script>console.log("gallery-detect", ' . json_encode(array('post' => $post_id, 'items' => count($gallery_items))) . ');</script>' . PHP_EOL;
    ?>
<main id="site-content" class="site-content single-activity-gallery">
    <h2 class="section-title">
         <span class="title-cn"><?php echo esc_html($page_title_cn); ?></span>
         <span class="title-eng">
            <?php
            if (isset($page_title_en) && trim($page_title_en) !== '') {
                echo esc_html($page_title_en);
            }
            else {
                echo esc_html($page_title_cn);
            }
            ?>
         </span>
    </h2>
    <div class="ga-body">
        <header class="sag-hero">
            <div class="sag-hero-inner container">
                <nav class="sag-nav-arrows" aria-label="<?php esc_attr_e('Post navigation', 'revamppage'); ?>">
                    <div class="sag-prev"><?php previous_post_link('%link', '&#8249;'); ?></div>
                    <div class="sag-gallery-title">
                        <h1 class="sag-title"><?php the_title(); ?></h1>
                        <div class="sag-meta">
                            <?php
                            $raw_date = get_the_date('Y-m-d');
                            $timestamp = strtotime($raw_date);
                            $date_cn = $timestamp
                                ? date_i18n('Y年m月d日', $timestamp)
                                : get_the_date('Y年m月d日');

                            $date_en = get_the_date('F j, Y');
                            ?>
                            <div class="sag-date sag-date-cn" style="display:none;"><?php echo esc_html__('發佈日期:', 'revamppage') . ' ' . esc_html($date_cn); ?></div>
                            <div class="sag-date sag-date-en"><?php echo esc_html__('Launch Date:', 'revamppage') . ' ' . esc_html($date_en); ?></div>
                        </div>
                    </div>
                    <div class="sag-next"><?php next_post_link('%link', '&#8250;'); ?></div>
                </nav>
            </div>
        </header>

        <div class="container sag-gallery-wrap">
            <?php if (!empty($gallery_items)): ?>
                <div class="sag-gallery">
                    <?php foreach ($gallery_items as $it): ?>
                        <div class="sag-gallery-item">
                            <?php
                            if ($it['type'] === 'attachment' && !empty($it['id'])) {
                                echo wp_get_attachment_image($it['id'], 'large', false, array('loading' => 'lazy'));
                            } elseif ($it['type'] === 'url' && !empty($it['url'])) {
                                echo '<img src="' . esc_url($it['url']) . '" alt="' . esc_attr(get_the_title()) . '" loading="lazy">';
                            }
                            ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="sag-content">
                    <?php the_content(); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <aside class="sag-related-wrap">
        <h2 class="sag-related-title">
           <span class="related-title-cn">相關活動</span>
           <span class="related-title-eng" style="display:none;">Related Activities</span>
        </h2>
        <div class="related-grid-background">
            <div class="sag-related-grid">
            <?php
            $cats = wp_get_post_categories(get_the_ID());
            $related_args = array(
                'post_type' => 'post',
                'posts_per_page' => 3,
                'post__not_in' => array(get_the_ID()),
            );
            if ($cats) {
                $related_args['category__in'] = $cats;
            }
            $related = new WP_Query($related_args);
            if ($related->have_posts()):
                while ($related->have_posts()):
                    $related->the_post(); ?>
                    <article class="sag-related-item">
                        <a href="<?php the_permalink(); ?>">
                            <div class="sag-related-thumb">
                                <?php echo wp_kses_post(revamppage_get_activity_image_html(get_the_ID(), 'medium')); ?>
                            </div>
                            <div class="sag-related-body">
                                <span class="related-title"><?php the_title(); ?></span>
                                <div class="related-date">
                                   <?php
                                   $raw_date = get_the_date('Y-m-d');
                                   $timestamp = strtotime($raw_date);
                                   $date_cn = $timestamp ? date_i18n('d/m/Y', $timestamp) : esc_html(get_the_date());
                                   $date_en = esc_html(get_the_date('F j, Y'));
                                   ?>
                                   <span class="date-cn">日期: <?php echo esc_html($date_cn); ?></span>
                                   <span class="date-eng" style="display:none;">Date: <?php echo $date_en; ?></span>
                                </div>
                            </div>
                        </a>
                    </article>
                <?php
                endwhile;
                wp_reset_postdata();
            else:
                ?><p class="sag-no-related"><?php esc_html_e('No related activities found.', 'revamppage'); ?></p><?php
            endif;
            ?>
        </div>
            </div>
    </aside>

    <!-- Gallery overlay (hidden by default) -->
    <div id="sag-overlay" class="sag-overlay" aria-hidden="true" role="dialog" aria-modal="true">
        <div class="sag-overlay__inner" role="document">
            <button class="sag-overlay__close" aria-label="<?php esc_attr_e('Close gallery', 'revamppage'); ?>">×</button>

            <button class="sag-overlay__nav sag-overlay__prev" aria-label="<?php esc_attr_e('Previous', 'revamppage'); ?>">‹</button>

            <div class="sag-overlay__track" tabindex="0" role="list">
                <?php if (!empty($gallery_items)): ?>
                    <?php foreach ($gallery_items as $idx => $it): ?>
                        <div class="sag-overlay__item" data-index="<?php echo (int) $idx; ?>" role="listitem">
                            <?php
                            if ($it['type'] === 'attachment' && !empty($it['id'])) {
                                echo wp_get_attachment_image($it['id'], 'large', false, array('loading' => 'lazy', 'alt' => get_the_title()));
                            } elseif ($it['type'] === 'url' && !empty($it['url'])) {
                                echo '<img src="' . esc_url($it['url']) . '" alt="' . esc_attr(get_the_title()) . '" loading="lazy">';
                            }
                            ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <button class="sag-overlay__nav sag-overlay__next" aria-label="<?php esc_attr_e('Next', 'revamppage'); ?>">›</button>
        </div>
    </div>
</main>

<?php
endwhile;

get_footer();