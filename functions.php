<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme setup: declare support for features.
 */
function revamppage_setup()
{
    add_theme_support('custom-background');
    add_theme_support('custom-logo');
    add_theme_support('post-thumbnails', array('activity', 'page', 'post'));

    register_nav_menus(array(
        'primary-menu-zh' => esc_html__('Primary Menu - Chinese', 'revamppage'),
        'primary-menu-en' => esc_html__('Primary Menu - English', 'revamppage'),
    ));
}
add_action('after_setup_theme', 'revamppage_setup');

/**
 * Register custom post type: Activity
 */
function revamppage_register_activity_post_type()
{
    $labels = array(
        'name' => esc_html__('Activities', 'revamppage'),
        'singular_name' => esc_html__('Activity', 'revamppage'),
        'menu_name' => esc_html__('Activities', 'revamppage'),
        'add_new' => esc_html__('Add New Activity', 'revamppage'),
        'add_new_item' => esc_html__('Add New Activity', 'revamppage'),
        'edit_item' => esc_html__('Edit Activity', 'revamppage'),
        'new_item' => esc_html__('New Activity', 'revamppage'),
        'view_item' => esc_html__('View Activity', 'revamppage'),
        'search_items' => esc_html__('Search Activities', 'revamppage'),
        'not_found' => esc_html__('No activities found', 'revamppage'),
        'not_found_in_trash' => esc_html__('No activities found in trash', 'revamppage'),
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'activity'),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-calendar-alt',
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
    );

    register_post_type('activity', $args);
}
add_action('init', 'revamppage_register_activity_post_type');

/**
 * Add custom meta boxes for Activity post type
 */
function revamppage_add_activity_meta_boxes()
{
    add_meta_box(
        'activity_details',
        esc_html__('Activity Details', 'revamppage'),
        'revamppage_activity_meta_box_callback',
        'activity',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'revamppage_add_activity_meta_boxes');

/**
 * Activity meta box callback
 */
function revamppage_activity_meta_box_callback($post)
{
    wp_nonce_field('revamppage_activity_nonce', 'revamppage_activity_nonce');

    $deadline = get_post_meta($post->ID, '_activity_deadline', true);
    $total_seats = get_post_meta($post->ID, '_activity_total_seats', true);
    $booked_seats = get_post_meta($post->ID, '_activity_booked_seats', true);
    $registration_url = get_post_meta($post->ID, '_activity_registration_url', true);
    $activity_code = get_post_meta($post->ID, '_activity_code', true);
    $activity_location = get_post_meta($post->ID, '_activity_location', true);
    $activity_time = get_post_meta($post->ID, '_activity_time', true);
    $activity_short_desc = get_post_meta($post->ID, '_activity_short_desc', true);
    $activity_popularity = get_post_meta($post->ID, '_activity_popularity', true);
    ?>

    <div style="padding: 10px 0;">
        <label for="activity_registration_url" style="display: block; font-weight: bold; margin-bottom: 5px;">
            <?php esc_html_e('Registration/Details Page URL', 'revamppage'); ?>
        </label>
        <input type="url" id="activity_registration_url" name="activity_registration_url" value="<?php echo esc_attr($registration_url); ?>" style="width: 100%; padding: 8px;">
        <small style="color: #666; margin-top: 5px; display: block;">Leave empty to use the activity page itself</small>
    </div>
    
    <div style="padding: 10px 0;">
        <label for="activity_deadline" style="display: block; font-weight: bold; margin-bottom: 5px;">
            <?php esc_html_e('Activity Deadline', 'revamppage'); ?>
        </label>
        <input type="date" id="activity_deadline" name="activity_deadline" value="<?php echo esc_attr($deadline); ?>" style="width: 100%; padding: 8px;">
    </div>

    <div style="padding: 10px 0;">
        <label for="activity_total_seats" style="display: block; font-weight: bold; margin-bottom: 5px;">
            <?php esc_html_e('Total Seats', 'revamppage'); ?>
        </label>
        <input type="number" id="activity_total_seats" name="activity_total_seats" value="<?php echo esc_attr($total_seats); ?>" min="0" style="width: 100%; padding: 8px;">
    </div>

    <div style="padding: 10px 0;">
        <label for="activity_booked_seats" style="display: block; font-weight: bold; margin-bottom: 5px;">
            <?php esc_html_e('Booked Seats', 'revamppage'); ?>
        </label>
        <input type="number" id="activity_booked_seats" name="activity_booked_seats" value="<?php echo esc_attr($booked_seats); ?>" min="0" style="width: 100%; padding: 8px;">
    </div>

    <div style="padding: 10px 0;">
        <label for="activity_code" style="display: block; font-weight: bold; margin-bottom: 5px;">
            <?php esc_html_e('Activity Code', 'revamppage'); ?>
        </label>
        <input type="text" id="activity_code" name="activity_code" value="<?php echo esc_attr($activity_code); ?>" style="width: 100%; padding: 8px;">
    </div>

    <div style="padding: 10px 0;">
        <label for="activity_location" style="display: block; font-weight: bold; margin-bottom: 5px;">
            <?php esc_html_e('Activity Location', 'revamppage'); ?>
        </label>
        <input type="text" id="activity_location" name="activity_location" value="<?php echo esc_attr($activity_location); ?>" style="width: 100%; padding: 8px;">
    </div>

    <div style="padding: 10px 0;">
        <label for="activity_time" style="display: block; font-weight: bold; margin-bottom: 5px;">
            <?php esc_html_e('Activity Time', 'revamppage'); ?>
        </label>
        <input type="text" id="activity_time" name="activity_time" value="<?php echo esc_attr($activity_time); ?>" placeholder="e.g., 02:00PM - 05:30PM" style="width: 100%; padding: 8px;">
    </div>

    <div style="padding: 10px 0;">
        <label for="activity_short_desc" style="display: block; font-weight: bold; margin-bottom: 5px;">
            <?php esc_html_e('Short Description', 'revamppage'); ?>
        </label>
        <textarea id="activity_short_desc" name="activity_short_desc" style="width: 100%; padding: 8px; min-height: 100px;"><?php echo esc_textarea($activity_short_desc); ?></textarea>
    </div>

    <div style="padding: 10px 0;">
        <label for="activity_popularity" style="display: block; font-weight: bold; margin-bottom: 5px;">
            <?php esc_html_e('Popularity Score (for sorting)', 'revamppage'); ?>
        </label>
        <input type="number" id="activity_popularity" name="activity_popularity" value="<?php echo esc_attr($activity_popularity); ?>" min="0" style="width: 100%; padding: 8px;">
    </div>

    <?php
}

/**
 * Save activity meta data
 */
function revamppage_save_activity_meta($post_id)
{
    if (!isset($_POST['revamppage_activity_nonce']) || !wp_verify_nonce($_POST['revamppage_activity_nonce'], 'revamppage_activity_nonce')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save all meta fields
    if (isset($_POST['activity_deadline'])) {
        update_post_meta($post_id, '_activity_deadline', sanitize_text_field($_POST['activity_deadline']));
    }

    if (isset($_POST['activity_total_seats'])) {
        update_post_meta($post_id, '_activity_total_seats', intval($_POST['activity_total_seats']));
    }

    if (isset($_POST['activity_booked_seats'])) {
        update_post_meta($post_id, '_activity_booked_seats', intval($_POST['activity_booked_seats']));
    }

    if (isset($_POST['activity_registration_url'])) {
        update_post_meta($post_id, '_activity_registration_url', esc_url_raw($_POST['activity_registration_url']));
    }

    if (isset($_POST['activity_code'])) {
        update_post_meta($post_id, '_activity_code', sanitize_text_field($_POST['activity_code']));
    }

    if (isset($_POST['activity_location'])) {
        update_post_meta($post_id, '_activity_location', sanitize_text_field($_POST['activity_location']));
    }

    if (isset($_POST['activity_time'])) {
        update_post_meta($post_id, '_activity_time', sanitize_text_field($_POST['activity_time']));
    }

    if (isset($_POST['activity_short_desc'])) {
        update_post_meta($post_id, '_activity_short_desc', wp_kses_post($_POST['activity_short_desc']));
    }

    if (isset($_POST['activity_popularity'])) {
        update_post_meta($post_id, '_activity_popularity', intval($_POST['activity_popularity']));
    }
}
add_action('save_post', 'revamppage_save_activity_meta');

/**
 * Menu fallback - displays list of pages when no menu is assigned
 */
function revamppage_menu_fallback()
{
    echo '<ul class="kwycc-menu-list">';

    wp_list_pages(array(
        'title_li' => '',
        'depth' => 2,
    ));

    echo '</ul>';
}

/**
 * Enqueue styles and scripts.
 */
function revamppage_enqueue()
{
    wp_enqueue_style('revamppage-style', get_stylesheet_uri());

    if (is_page_template('page-past-activities.php')) {
        wp_enqueue_style(
            'revamppage-past-activities',
            get_stylesheet_directory_uri() . '/css/past-activities.css',
            array('revamppage-style'),
            '1.0'
        );

        wp_enqueue_script(
            'revamppage-past-activities',
            get_stylesheet_directory_uri() . '/js/past-activities.js',
            array(),
            '1.0',
            true
        );
    }

    wp_enqueue_style(
        'revamppage-other-information',
        get_stylesheet_directory_uri() . '/css/other-information.css',
        array('revamppage-style'),
        '1.0'
    );

    wp_enqueue_script(
        'revamppage-other-information',
        get_stylesheet_directory_uri() . '/js/other-information.js',
        array(),
        '1.0',
        true
    );

    wp_enqueue_script(
        'revamppage-hero',
        get_stylesheet_directory_uri() . '/js/kwycc-hero.js',
        array(),
        '1.0',
        true
    );

    wp_enqueue_script(
        'revamppage-nav',
        get_stylesheet_directory_uri() . '/js/kwycc-nav.js',
        array(),
        '1.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'revamppage_enqueue');

/**
 * Enqueue activity page scripts and styles
 */
function revamppage_enqueue_activity_scripts()
{
    if (is_post_type_archive('activity') || is_singular('activity')) {
        wp_enqueue_script(
            'revamppage-activity',
            get_stylesheet_directory_uri() . '/js/kwycc-activity.js',
            array(),
            '1.0',
            true
        );

        // Localize script for AJAX
        wp_localize_script('revamppage-activity', 'revamppage_vars', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('activity_signup_nonce')
        ));
    }
}
add_action('wp_enqueue_scripts', 'revamppage_enqueue_activity_scripts');

/**
 * Handle activity signup form submission via AJAX
 */
function revamppage_handle_activity_signup()
{
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'activity_signup_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        wp_die();
    }

    // Get post ID
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

    if (!$post_id || get_post_type($post_id) !== 'activity') {
        wp_send_json_error(array('message' => 'Invalid activity'));
        wp_die();
    }

    // Sanitize form data
    $signup_data = array(
        'post_id' => $post_id,
        'chinese_name' => sanitize_text_field($_POST['chinese_name'] ?? ''),
        'english_name' => sanitize_text_field($_POST['english_name'] ?? ''),
        'phone' => sanitize_text_field($_POST['phone'] ?? ''),
        'id_number' => sanitize_text_field($_POST['id_number'] ?? ''),
        'category' => sanitize_text_field($_POST['category'] ?? ''),
        'category_confirm' => sanitize_text_field($_POST['category_confirm'] ?? ''),
        'signup_date' => current_time('mysql')
    );

    // Validate required fields
    if (empty($signup_data['chinese_name']) || empty($signup_data['phone']) || empty($signup_data['id_number'])) {
        wp_send_json_error(array('message' => 'Please fill in all required fields'));
        wp_die();
    }

    // Update booked seats count
    $booked_seats = (int) get_post_meta($post_id, '_activity_booked_seats', true);
    $total_seats = (int) get_post_meta($post_id, '_activity_total_seats', true);

    if ($booked_seats >= $total_seats) {
        wp_send_json_error(array('message' => 'Activity is fully booked'));
        wp_die();
    }

    // Increment booked seats
    update_post_meta($post_id, '_activity_booked_seats', $booked_seats + 1);

    // Save signup data (you can expand this to store in a custom table or email)
    // For now, we'll just return success
    do_action('revamppage_activity_signup', $signup_data);

    wp_send_json_success(array(
        'message' => 'Sign up successful',
        'activity_id' => $post_id
    ));
    wp_die();
}
add_action('wp_ajax_submit_activity_signup', 'revamppage_handle_activity_signup');
add_action('wp_ajax_nopriv_submit_activity_signup', 'revamppage_handle_activity_signup');

/**
 * Hook for custom activity signup handling (can be extended by child theme)
 */
function revamppage_activity_signup_email($signup_data)
{
    // You can extend this to send email notifications
    // Example: wp_mail($admin_email, 'New Activity Signup', $message);
}
add_action('revamppage_activity_signup', 'revamppage_activity_signup_email');

/**
 * Add body class for activity archive pages
 */
function revamppage_body_classes($classes)
{
    if (is_post_type_archive('activity')) {
        $classes[] = 'is-activity-page';
    }
    return $classes;
}
add_filter('body_class', 'revamppage_body_classes');

/**
 * ----------------------
 * Contact page: enqueue CSS when using contact.php template
 * ----------------------
 */
function revamppage_enqueue_contact_assets()
{
    // Ensure conditional check only runs during front-end rendering
    if (is_page() && is_page_template('contact.php')) {
        wp_enqueue_style('revamppage-contact', get_stylesheet_directory_uri() . '/css/contact.css', array('revamppage-style'), '1.0');
    }
}
add_action('wp_enqueue_scripts', 'revamppage_enqueue_contact_assets');

/**
 * Handle contact form submission (admin-post)
 */
function revamppage_handle_contact_form()
{
    // Verify nonce
    if (!isset($_POST['revamppage_contact_nonce']) || !wp_verify_nonce($_POST['revamppage_contact_nonce'], 'revamppage_contact_nonce')) {
        $redirect = wp_get_referer() ? wp_get_referer() : home_url();
        wp_safe_redirect(add_query_arg('contact', 'error', $redirect));
        exit;
    }

    $name = sanitize_text_field($_POST['contact_name'] ?? '');
    $email = sanitize_email($_POST['contact_email'] ?? '');
    $subject = sanitize_text_field($_POST['contact_subject'] ?? __('Contact Form Message', 'revamppage'));
    $message = sanitize_textarea_field($_POST['contact_message'] ?? '');

    if (empty($name) || empty($email) || empty($message) || !is_email($email)) {
        $redirect = wp_get_referer() ? wp_get_referer() : home_url();
        wp_safe_redirect(add_query_arg('contact', 'error', $redirect));
        exit;
    }

    $to = get_option('admin_email');
    $headers = array('Reply-To: ' . $name . ' <' . $email . '>');
    $body = "Name: {$name}\nEmail: {$email}\n\nMessage:\n{$message}";

    // Attempt to send email
    $sent = wp_mail($to, $subject, $body, $headers);

    $redirect = wp_get_referer() ? wp_get_referer() : home_url();
    if ($sent) {
        wp_safe_redirect(add_query_arg('contact', 'success', $redirect));
    } else {
        wp_safe_redirect(add_query_arg('contact', 'error', $redirect));
    }
    exit;
}
add_action('admin_post_nopriv_revamppage_contact', 'revamppage_handle_contact_form');
add_action('admin_post_revamppage_contact', 'revamppage_handle_contact_form');

/**
 * Enqueue About page CSS only when the About page is displayed.
 * More robust: checks page template meta, slug or page ID.
 */
function revamppage_enqueue_about_assets()
{
    if (!is_page()) {
        return;
    }

    global $post;
    if (!$post instanceof WP_Post) {
        return;
    }

    // 1) Template assigned in Page Attributes (meta stored in _wp_page_template)
    $template = get_post_meta($post->ID, '_wp_page_template', true);

    // 2) Page slug and ID checks as fallback
    $slug = $post->post_name;
    $page_id = (int) $post->ID;

    // Add your About page ID here if you want an exact match, e.g. 42
    $about_page_ids = array(); // e.g. array(42);

    if (
        $template === 'page-about-us.php'
        || $slug === 'about-us'
        || $slug === 'about'
        || in_array($page_id, $about_page_ids, true)
    ) {
        wp_enqueue_style(
            'revamppage-about',
            get_stylesheet_directory_uri() . '/css/about.css',
            array('revamppage-style'),
            '1.0'
        );

        // Enqueue the about page lightbox script
        wp_enqueue_script(
            'revamppage-about-lightbox',
            get_stylesheet_directory_uri() . '/js/about-lightbox.js',
            array(),
            '1.0',
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'revamppage_enqueue_about_assets');

function revamppage_get_activity_image_html($post_id, $size = 'medium')
{
    $post_id = absint($post_id);
    $alt = get_the_title($post_id);
    $alt = $alt ? $alt : __('Post image', 'revamppage');

    // 1) Featured image
    if (has_post_thumbnail($post_id)) {
        return get_the_post_thumbnail($post_id, $size, array(
            'alt' => $alt,
            'loading' => 'lazy',
            'class' => 'revamppage-activity-image',
        ));
    }

    // 2) Attached images
    $attachments = get_attached_media('image', $post_id);
    if (!empty($attachments)) {
        $first_attachment = reset($attachments);
        return wp_get_attachment_image($first_attachment->ID, $size, false, array(
            'alt' => $alt,
            'loading' => 'lazy',
            'class' => 'revamppage-activity-image',
        ));
    }

    // 3) Try to find images from post content (src / data-src / srcset)
    $content = get_post_field('post_content', $post_id, 'raw');
    if (!empty($content)) {
        $image_url = '';

        preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $matches);
        if (!empty($matches[1])) {
            $image_url = $matches[1];
        }

        if (empty($image_url)) {
            preg_match('/<img[^>]+data-src=["\']([^"\']+)["\']/i', $content, $data_matches);
            if (!empty($data_matches[1])) {
                $image_url = $data_matches[1];
            }
        }

        if (empty($image_url)) {
            preg_match('/<img[^>]+srcset=["\']([^"\']+)["\']/i', $content, $srcset_matches);
            if (!empty($srcset_matches[1])) {
                $srcset = $srcset_matches[1];
                $srcset_parts = preg_split('/\s*,\s*/', $srcset);
                if (!empty($srcset_parts[0])) {
                    $image_url = trim($srcset_parts[0]);
                    $image_url = preg_replace('/\s+\d+w$/i', '', $image_url);
                    $image_url = preg_replace('/\s+\d+h$/i', '', $image_url);
                }
            }
        }

        if (!empty($image_url)) {
            return '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($alt) . '" loading="lazy" class="revamppage-activity-image">';
        }

        // 4) Gallery shortcode fallback
        if (has_shortcode($content, 'gallery')) {
            $gallery_images = get_post_gallery_images($post_id);
            if (!empty($gallery_images[0])) {
                return '<img src="' . esc_url($gallery_images[0]) . '" alt="' . esc_attr($alt) . '" loading="lazy" class="revamppage-activity-image">';
            }
        }
    }

    // 5) Placeholder
    $placeholder_path = get_stylesheet_directory() . '/images/placeholder-about.png';
    $placeholder_url = get_stylesheet_directory_uri() . '/images/placeholder-about.png';

    if (!file_exists($placeholder_path)) {
        $placeholder_path = get_stylesheet_directory() . '/images/placeholder.png';
        $placeholder_url = get_stylesheet_directory_uri() . '/images/placeholder.png';
    }

    return '<img src="' . esc_url($placeholder_url) . '" alt="' . esc_attr($alt) . '" loading="lazy" class="revamppage-activity-image">';
}

function revamppage_enqueue_past_activities_assets()
{
    if (is_page_template('page-past-activities.php')) {
        wp_enqueue_style(
            'revamppage-past-activities',
            get_stylesheet_directory_uri() . '/css/past-activities.css',
            array('revamppage-style'),
            '1.0'
        );

        wp_enqueue_script(
            'revamppage-past-activities',
            get_stylesheet_directory_uri() . '/js/past-activities.js',
            array(),
            '1.0',
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'revamppage_enqueue_past_activities_assets');


// Enqueue gallery CSS + helper JS for single posts that should use the gallery layout
function revamppage_enqueue_single_gallery_assets()
{
    if (!is_singular()) {
        return;
    }

    $post_id = get_queried_object_id();
    if (!$post_id) {
        return;
    }

    // 1) Featured image
    $has_thumbnail = has_post_thumbnail($post_id);

    // 2) Attached images (uploaded and attached to this post)
    $attached = get_posts(array(
        'post_type' => 'attachment',
        'post_mime_type' => 'image',
        'posts_per_page' => 1,
        'post_parent' => $post_id,
        'fields' => 'ids',
    ));
    $has_attached = !empty($attached);

    // 3) Gallery shortcode images
    $gallery_images = get_post_gallery_images($post_id);
    $has_gallery_images = !empty($gallery_images);

    // 4) Inline <img> in raw content
    $content = get_post_field('post_content', $post_id, 'raw');
    $has_inline_imgs = $content && preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $content);

    // 5) Gutenberg blocks: core/gallery or core/image (covers block editor usage)
    $has_block_images = false;
    if (function_exists('parse_blocks') && !empty($content)) {
        $blocks = parse_blocks($content);
        $check_blocks = function ($blocks) use (&$check_blocks) {
            foreach ($blocks as $b) {
                if (empty($b['blockName'])) {
                    if (!empty($b['innerBlocks']) && $check_blocks($b['innerBlocks'])) {
                        return true;
                    }
                    continue;
                }
                if (in_array($b['blockName'], array('core/gallery', 'core/image'), true)) {
                    return true;
                }
                if (!empty($b['innerBlocks']) && $check_blocks($b['innerBlocks'])) {
                    return true;
                }
            }
            return false;
        };
        $has_block_images = $check_blocks($blocks);
    }

    // Final decision
    $should_enqueue = $has_thumbnail || $has_attached || $has_gallery_images || $has_inline_imgs || $has_block_images;

    // Debug comment (remove in production)
    echo '<!-- gallery-detect: post=' . (int) $post_id
        . ' thumb=' . (int) $has_thumbnail
        . ' attached=' . (int) $has_attached
        . ' gallery_shortcut=' . (int) $has_gallery_images
        . ' inline_img=' . (int) $has_inline_imgs
        . ' block_img=' . (int) $has_block_images
        . ' -->' . PHP_EOL;

    if ($should_enqueue) {
        wp_enqueue_style(
            'revamppage-single-activity',
            get_stylesheet_directory_uri() . '/css/single-activity-gallery.css',
            array('revamppage-style'),
            '1.0'
        );

        wp_enqueue_script(
            'revamppage-single-activity',
            get_stylesheet_directory_uri() . '/js/single-activity.js',
            array(),
            '1.0',
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'revamppage_enqueue_single_gallery_assets', 20);