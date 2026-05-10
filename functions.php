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
    ?>

    <div style="padding: 10px 0;">
        <label for="activity_registration_url" style="display: block; font-weight: bold; margin-bottom: 5px;">
            <?php esc_html_e('Registration/Details Page URL', 'revamppage'); ?>
        </label>
        <input type="url" id="activity_registration_url" name="activity_registration_url" value="<?php echo esc_attr(get_post_meta($post->ID, '_activity_registration_url', true)); ?>" style="width: 100%; padding: 8px;">
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

    if (isset($_POST['activity_deadline'])) {
        update_post_meta($post_id, '_activity_deadline', sanitize_text_field($_POST['activity_deadline']));
    }

    if (isset($_POST['activity_total_seats'])) {
        update_post_meta($post_id, '_activity_total_seats', intval($_POST['activity_total_seats']));
    }

    if (isset($_POST['activity_booked_seats'])) {
        update_post_meta($post_id, '_activity_booked_seats', intval($_POST['activity_booked_seats']));
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