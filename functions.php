<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme setup: declare support for features.
 */
function revamppage_setup()
{
    // Allow WP Customizer background image/color
    add_theme_support('custom-background');

    // Allow custom logo
    add_theme_support('custom-logo');

    // Add featured image support
    add_theme_support('post-thumbnails', array('activity'));

    // Register navigation menus for different languages
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
    // Main stylesheet
    wp_enqueue_style('revamppage-style', get_stylesheet_uri());

    // Hero behaviour (center detection + active scaling)
    wp_enqueue_script(
        'revamppage-hero',
        get_stylesheet_directory_uri() . '/js/kwycc-hero.js',
        array(),
        '1.0',
        true
    );

    // Navigation & language toggle functionality
    wp_enqueue_script(
        'revamppage-nav',
        get_stylesheet_directory_uri() . '/js/kwycc-nav.js',
        array(),
        '1.0',
        true
    );

    // Footer functionality
    wp_enqueue_script(
        'revamppage-footer',
        get_stylesheet_directory_uri() . '/js/kwycc-footer.js',
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