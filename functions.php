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
 * Register custom post type: Smartteen Book
 */
function revamppage_register_smartteen_post_type()
{
    $labels = array(
        'name' => esc_html__('Smartteen Books', 'revamppage'),
        'singular_name' => esc_html__('Smartteen Book', 'revamppage'),
        'menu_name' => esc_html__('Smartteen Books', 'revamppage'),
        'add_new' => esc_html__('Add New Smartteen Book', 'revamppage'),
        'add_new_item' => esc_html__('Add New Smartteen Book', 'revamppage'),
        'edit_item' => esc_html__('Edit Smartteen Book', 'revamppage'),
        'new_item' => esc_html__('New Smartteen Book', 'revamppage'),
        'view_item' => esc_html__('View Smartteen Book', 'revamppage'),
        'search_items' => esc_html__('Search Smartteen Books', 'revamppage'),
        'not_found' => esc_html__('No smartteen books found', 'revamppage'),
        'not_found_in_trash' => esc_html__('No smartteen books found in trash', 'revamppage'),
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'smartteen-book'),
        'capability_type' => 'post',
        'has_archive' => false,
        'hierarchical' => false,
        'menu_position' => 6,
        'menu_icon' => 'dashicons-book-alt',
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
    );

    register_post_type('smartteen', $args);
}
add_action('init', 'revamppage_register_smartteen_post_type');

function revamppage_add_smartteen_meta_boxes()
{
    add_meta_box(
        'revamppage_smartteen_details',
        esc_html__('Smartteen Book Details', 'revamppage'),
        'revamppage_smartteen_meta_box_callback',
        'smartteen',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'revamppage_add_smartteen_meta_boxes');

/**
 * Enqueue media scripts for smartteen post edit screens
 */
function revamppage_admin_enqueue($hook) {
    if ($hook !== 'post.php' && $hook !== 'post-new.php') return;
    $screen = get_current_screen();
    if (!$screen) return;
    if ($screen->post_type !== 'smartteen') return;
    // Enqueue WP media scripts so wp.media is available in meta box
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'revamppage_admin_enqueue');

function revamppage_smartteen_meta_box_callback($post)
{
    wp_nonce_field('revamppage_smartteen_nonce', 'revamppage_smartteen_nonce');

    $intro = get_post_meta($post->ID, '_smartteen_intro', true);
    $pages = get_post_meta($post->ID, '_smartteen_pages', true);
    $pdf_url = get_post_meta($post->ID, '_smartteen_pdf', true);
    if (!is_array($pages)) {
        $pages = array();
    }

    // custom thumbnail attachment id (optional)
    $thumb_id = get_post_meta($post->ID, '_smartteen_thumb_id', true);
    $thumb_html = '';
    if ($thumb_id) {
        $thumb_html = wp_get_attachment_image($thumb_id, 'thumbnail', false, array('style' => 'max-width:160px; height:auto; display:block;'));
    }
    ?>
    <div class="revamppage-smartteen-meta">
        <p><?php esc_html_e('Cover image: choose a thumbnail specifically for the carousel (optional). If not set, the Featured Image will be used. You can also use the site Document Gallery thumbnail settings by filtering the cover size.', 'revamppage'); ?></p>

        <div style="margin-bottom:16px; display:flex; gap:1rem; align-items:flex-start;">
            <div id="revamppage-smartteen-thumb-preview" style="min-width:160px;"><?php echo $thumb_html; ?></div>
            <div>
                <input type="hidden" id="smartteen_thumb_id" name="smartteen_thumb_id" value="<?php echo esc_attr($thumb_id); ?>">
                <button type="button" class="button" id="revamppage-select-thumb"><?php esc_html_e('Select Thumbnail', 'revamppage'); ?></button>
                <button type="button" class="button" id="revamppage-remove-thumb" style="display:<?php echo $thumb_id ? 'inline-block' : 'none'; ?>; margin-left:8px;"><?php esc_html_e('Remove', 'revamppage'); ?></button>
                <p style="margin-top:8px; color:#666;"><?php esc_html_e('If empty, Featured Image will be used as cover.'); ?></p>
            </div>
        </div>

        <div style="margin-bottom: 16px;">
            <label for="smartteen_intro" style="display:block; font-weight:700; margin-bottom:6px;">
                <?php esc_html_e('Introduction', 'revamppage'); ?>
            </label>
            <textarea id="smartteen_intro" name="smartteen_intro" rows="4" style="width:100%; padding:8px; font-size:14px;"><?php echo esc_textarea($intro); ?></textarea>
        </div>

        <div style="margin-bottom: 16px;">
            <label for="smartteen_pdf_url" style="display:block; font-weight:700; margin-bottom:6px;">
                <?php esc_html_e('PDF URL (optional) - previewed in overlay', 'revamppage'); ?>
            </label>
            <input type="url" id="smartteen_pdf_url" name="smartteen_pdf_url" value="<?php echo esc_attr($pdf_url); ?>" style="width:100%; padding:8px;" placeholder="https://example.com/book.pdf">
            <p style="margin-top:8px; color:#666;"><?php esc_html_e('Provide a direct PDF URL to allow users to preview the book in the overlay.', 'revamppage'); ?></p>
        </div>

        <div id="revamppage-smartteen-pages">
            <h4 style="margin-bottom: 12px;"><?php esc_html_e('Book Pages', 'revamppage'); ?></h4>
            <?php if (empty($pages)): ?>
                <div class="revamppage-smartteen-page-item">
                    <div style="margin-bottom: 8px;"><strong><?php esc_html_e('Page 1', 'revamppage'); ?></strong></div>
                    <label style="display:block; margin-bottom:4px; font-weight:600;"><?php esc_html_e('Page title', 'revamppage'); ?></label>
                    <input type="text" name="smartteen_page_titles[]" value="" style="width:100%; padding:8px; margin-bottom:8px;">
                    <label style="display:block; margin-bottom:4px; font-weight:600;"><?php esc_html_e('Page content', 'revamppage'); ?></label>
                    <textarea name="smartteen_page_contents[]" rows="4" style="width:100%; padding:8px; margin-bottom:8px;"></textarea>
                    <label style="display:block; margin-bottom:4px; font-weight:600;"><?php esc_html_e('Optional page image URL', 'revamppage'); ?></label>
                    <input type="url" name="smartteen_page_images[]" value="" style="width:100%; padding:8px;">
                    <button type="button" class="button revamppage-remove-smartteen-page" style="margin-top:10px; display:none;"><?php esc_html_e('Remove page', 'revamppage'); ?></button>
                </div>
            <?php else: ?>
                <?php foreach ($pages as $page_index => $page_item): ?>
                    <div class="revamppage-smartteen-page-item" style="border:1px solid #ddd; padding:12px; margin-bottom:12px; border-radius:4px;">
                        <div style="margin-bottom:8px;"><strong><?php echo sprintf(esc_html__('Page %d', 'revamppage'), $page_index + 1); ?></strong></div>
                        <label style="display:block; margin-bottom:4px; font-weight:600;"><?php esc_html_e('Page title', 'revamppage'); ?></label>
                        <input type="text" name="smartteen_page_titles[]" value="<?php echo esc_attr($page_item['title'] ?? ''); ?>" style="width:100%; padding:8px; margin-bottom:8px;">
                        <label style="display:block; margin-bottom:4px; font-weight:600;"><?php esc_html_e('Page content', 'revamppage'); ?></label>
                        <textarea name="smartteen_page_contents[]" rows="4" style="width:100%; padding:8px; margin-bottom:8px;"><?php echo esc_textarea($page_item['content'] ?? ''); ?></textarea>
                        <label style="display:block; margin-bottom:4px; font-weight:600;"><?php esc_html_e('Optional page image URL', 'revamppage'); ?></label>
                        <input type="url" name="smartteen_page_images[]" value="<?php echo esc_attr($page_item['image'] ?? ''); ?>" style="width:100%; padding:8px;">
                        <button type="button" class="button revamppage-remove-smartteen-page" style="margin-top:10px;"><?php esc_html_e('Remove page', 'revamppage'); ?></button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <button type="button" class="button button-primary" id="revamppage-add-smartteen-page" style="margin-top:8px;">
            <?php esc_html_e('Add Page', 'revamppage'); ?>
        </button>
    </div>
    <script>
        (function () {
            var container = document.getElementById('revamppage-smartteen-pages');
            var template = document.createElement('div');
            template.className = 'revamppage-smartteen-page-item';
            template.innerHTML = '<div style="margin-bottom:8px;"><strong>Page {{pageNumber}}</strong></div>' +
                '<label style="display:block; margin-bottom:4px; font-weight:600;"><?php echo esc_js(esc_html__('Page title', 'revamppage')); ?></label>' +
                '<input type="text" name="smartteen_page_titles[]" value="" style="width:100%; padding:8px; margin-bottom:8px;">' +
                '<label style="display:block; margin-bottom:4px; font-weight:600;"><?php echo esc_js(esc_html__('Page content', 'revamppage')); ?></label>' +
                '<textarea name="smartteen_page_contents[]" rows="4" style="width:100%; padding:8px; margin-bottom:8px;"></textarea>' +
                '<label style="display:block; margin-bottom:4px; font-weight:600;"><?php echo esc_js(esc_html__('Optional page image URL', 'revamppage')); ?></label>' +
                '<input type="url" name="smartteen_page_images[]" value="" style="width:100%; padding:8px;">' +
                '<button type="button" class="button revamppage-remove-smartteen-page" style="margin-top:10px;"><?php echo esc_js(esc_html__('Remove page', 'revamppage')); ?></button>';

            function updatePageHeaders() {
                var items = container.querySelectorAll('.revamppage-smartteen-page-item');
                items.forEach(function (item, index) {
                    var header = item.querySelector('strong');
                    if (header) {
                        header.textContent = 'Page ' + (index + 1);
                    }
                });
            }

            function attachRemoveButton(button) {
                button.addEventListener('click', function () {
                    var item = button.closest('.revamppage-smartteen-page-item');
                    if (item) {
                        item.parentNode.removeChild(item);
                        updatePageHeaders();
                    }
                });
            }

            container.querySelectorAll('.revamppage-remove-smartteen-page').forEach(function (btn) {
                attachRemoveButton(btn);
            });

            document.getElementById('revamppage-add-smartteen-page').addEventListener('click', function () {
                var newItem = template.cloneNode(true);
                newItem.style.border = '1px solid #ddd';
                newItem.style.padding = '12px';
                newItem.style.marginBottom = '12px';
                container.appendChild(newItem);
                attachRemoveButton(newItem.querySelector('.revamppage-remove-smartteen-page'));
                updatePageHeaders();
            });

            // Media selector for thumbnail
            var frame = null;
            var selectBtn = document.getElementById('revamppage-select-thumb');
            var removeBtn = document.getElementById('revamppage-remove-thumb');
            var preview = document.getElementById('revamppage-smartteen-thumb-preview');
            var input = document.getElementById('smartteen_thumb_id');

            if (selectBtn) {
                selectBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    if (frame) frame.open();
                    else {
                        frame = wp.media({ title: '<?php echo esc_js(esc_html__('Select thumbnail', 'revamppage')); ?>', multiple: false });
                        frame.on('select', function () {
                            var att = frame.state().get('selection').first().toJSON();
                            if (att && att.id) {
                                input.value = att.id;
                        // Preview the original uploaded image (no cropping) to match carousel behavior
                        var src = att.url || (att.sizes && (att.sizes.full && att.sizes.full.url)) || '';
                        preview.innerHTML = '<img src="' + src + '" style="max-width:160px; display:block; height:auto;">';
                        removeBtn.style.display = 'inline-block';
                            }
                        });
                        frame.open();
                    }
                });
            }

            if (removeBtn) {
                removeBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    input.value = '';
                    preview.innerHTML = '';
                    removeBtn.style.display = 'none';
                });
            }
        })();
    </script>
    <?php
}

function revamppage_save_smartteen_meta($post_id)
{
    if (!isset($_POST['revamppage_smartteen_nonce']) || !wp_verify_nonce($_POST['revamppage_smartteen_nonce'], 'revamppage_smartteen_nonce')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (get_post_type($post_id) !== 'smartteen') {
        return;
    }

    if (isset($_POST['smartteen_intro'])) {
        update_post_meta($post_id, '_smartteen_intro', sanitize_textarea_field($_POST['smartteen_intro']));
    }

    $pages = array();
    $titles = isset($_POST['smartteen_page_titles']) && is_array($_POST['smartteen_page_titles']) ? $_POST['smartteen_page_titles'] : array();
    $contents = isset($_POST['smartteen_page_contents']) && is_array($_POST['smartteen_page_contents']) ? $_POST['smartteen_page_contents'] : array();
    $images = isset($_POST['smartteen_page_images']) && is_array($_POST['smartteen_page_images']) ? $_POST['smartteen_page_images'] : array();

    foreach ($contents as $index => $content) {
        $title = sanitize_text_field($titles[$index] ?? '');
        $image = esc_url_raw($images[$index] ?? '');
        $content = wp_kses_post($content);
        if ($title !== '' || $content !== '' || $image !== '') {
            $pages[] = array(
                'title' => $title,
                'content' => $content,
                'image' => $image,
            );
        }
    }

    if (!empty($pages)) {
        update_post_meta($post_id, '_smartteen_pages', $pages);
    } else {
        delete_post_meta($post_id, '_smartteen_pages');
    }

    // Save optional thumbnail attachment id
    if (isset($_POST['smartteen_thumb_id']) && $_POST['smartteen_thumb_id'] !== '') {
        $thumb_id = intval($_POST['smartteen_thumb_id']);
        if ($thumb_id > 0 && get_post_status($thumb_id)) {
            update_post_meta($post_id, '_smartteen_thumb_id', $thumb_id);
        }
    } else {
        delete_post_meta($post_id, '_smartteen_thumb_id');
    }

    // Save optional PDF URL for overlay preview
    if (isset($_POST['smartteen_pdf_url'])) {
        $pdf = trim($_POST['smartteen_pdf_url']);
        if ($pdf !== '') {
            update_post_meta($post_id, '_smartteen_pdf', esc_url_raw($pdf));
        } else {
            delete_post_meta($post_id, '_smartteen_pdf');
        }
    }
}


add_action('save_post', 'revamppage_save_smartteen_meta');

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

        wp_localize_script(
            'revamppage-past-activities',
            'revamppagePastActivities',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'ajax_action' => 'revamppage_filter_past_activities',
                'nonce' => wp_create_nonce('revamppage_past_activities'),
            )
        );
    }

    if (is_page_template('page-smartteen.php')) {
        wp_enqueue_style(
            'revamppage-smartteen',
            get_stylesheet_directory_uri() . '/css/smartteen.css',
            array('revamppage-style'),
            '1.0'
        );

        wp_enqueue_script(
            'revamppage-smartteen',
            get_stylesheet_directory_uri() . '/js/smartteen.js',
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

function revamppage_get_current_language_code($fallback = '')
{
    $lang = '';

    if (!empty($fallback)) {
        $lang = sanitize_text_field(wp_unslash($fallback));
    }

    if (empty($lang) && isset($_POST['lang'])) {
        $lang = sanitize_text_field(wp_unslash($_POST['lang']));
    }

    if (empty($lang) && isset($_GET['lang'])) {
        $lang = sanitize_text_field(wp_unslash($_GET['lang']));
    }

    if (empty($lang) && function_exists('apply_filters')) {
        $wpml_lang = apply_filters('wpml_current_language', null);
        if (!empty($wpml_lang)) {
            $lang = sanitize_text_field(wp_unslash($wpml_lang));
        }
    }

    if (empty($lang) && function_exists('pll_current_language')) {
        $pll_lang = pll_current_language('slug');
        if (!empty($pll_lang)) {
            $lang = sanitize_text_field(wp_unslash($pll_lang));
        }
    }

    if (empty($lang)) {
        $locale = get_locale();
        if (!empty($locale)) {
            $locale = strtolower($locale);
            if (strpos($locale, 'en') !== false) {
                $lang = 'en';
            } elseif (strpos($locale, 'zh') !== false || strpos($locale, 'hk') !== false || strpos($locale, 'tw') !== false || strpos($locale, 'cn') !== false) {
                $lang = 'zh';
            }
        }
    }

    if (empty($lang) && !empty($_COOKIE['revamppage_lang'])) {
        $lang = sanitize_text_field(wp_unslash($_COOKIE['revamppage_lang']));
    }

    if (empty($lang)) {
        $lang = 'zh';
    }

    return $lang;
}

function revamppage_get_language_code_for_query($lang = '')
{
    $lang = revamppage_get_current_language_code($lang);
    if (empty($lang)) {
        return '';
    }

    $lang = strtolower($lang);

    if (in_array($lang, array('en', 'en_us', 'en-us', 'english'), true)) {
        return 'en_US';
    }

    if (in_array($lang, array('zh', 'zh_hk', 'zh-hk', 'hk', 'traditional', 'traditional_chinese'), true)) {
        return 'zh_HK';
    }

    if (in_array($lang, array('zh_cn', 'zh-cn', 'cn', 'simplified', 'simplified_chinese'), true)) {
        return 'zh_CN';
    }

    return $lang;
}

function revamppage_get_language_query_args($lang = '')
{
    $lang_code = revamppage_get_language_code_for_query($lang);
    if (empty($lang_code)) {
        return array();
    }

    return array('lang' => $lang_code);
}

function revamppage_build_past_activities_query_args($page_id, $paged, $posts_per_page = 8, $filter_values = array())
{
    $args = array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => $posts_per_page,
        'paged' => max(1, absint($paged)),
        'orderby' => 'date',
        'order' => 'DESC',
        'date_query' => array(
            array(
                'after' => array(
                    'year' => 2020,
                    'month' => 1,
                    'day' => 1,
                ),
                'inclusive' => true,
            ),
        ),
    );

    $language_query_args = revamppage_get_language_query_args(isset($filter_values['lang']) ? $filter_values['lang'] : '');
    if (!empty($language_query_args)) {
        $args = array_merge($args, $language_query_args);
    }

    $cat = isset($filter_values['cat']) ? absint($filter_values['cat']) : 0;
    if ($cat > 0) {
        $args['cat'] = $cat;
    }

    $year = isset($filter_values['year']) ? absint($filter_values['year']) : 0;
    if ($year > 0) {
        $args['year'] = $year;
    }

    $month = isset($filter_values['month']) ? absint($filter_values['month']) : 0;
    if ($month > 0) {
        $args['monthnum'] = $month;
    }

    $search = isset($filter_values['s']) ? sanitize_text_field(wp_unslash($filter_values['s'])) : '';
    if ($search !== '') {
        $args['s'] = $search;
    }

    return $args;
}

function revamppage_render_past_activities_markup($pa_query, $paged, $page_id, $filter_values = array())
{
    ob_start();

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
                        <?php
                        if (function_exists('revamppage_get_activity_image_html')) {
                            echo wp_kses_post(revamppage_get_activity_image_html(get_the_ID(), 'medium'));
                        } elseif (has_post_thumbnail()) {
                            echo get_the_post_thumbnail(get_the_ID(), 'medium');
                        }
                        ?>
                    </div>
                    <div class="pa-card-body">
                        <?php if (!empty($category_name)): ?>
                        <?php endif; ?>

                        <h3 class="pa-card-title">
                            <span class="pa-title-cn"><?php echo esc_html(get_the_title()); ?></span>
                            <span class="pa-title-en" style="display:none;"><?php echo esc_html(get_the_title()); ?></span>
                        </h3>

                        <div class="pa-card-meta">
                            <?php
                            $raw_date = get_the_date('Y-m-d');
                            $timestamp = strtotime($raw_date);
                            $date_cn = $timestamp ? date_i18n('d/m/Y', $timestamp) : esc_html(get_the_date());
                            $date_en = esc_html(get_the_date('F j, Y'));
                            ?>
                            <span class="pa-date-cn">日期: <?php echo esc_html($date_cn); ?></span>
                            <span class="pa-date-en" style="display:none;">Date: <?php echo $date_en; ?></span>
                        </div>
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
        <?php
    endif;

    $grid_html = ob_get_clean();

    $pagination_html = '';
    $pagination_info_html = '';

    if ($pa_query->found_posts > 0) {
        $pagination_query_args = array(
            'page_id' => absint($page_id),
            'lang' => revamppage_get_language_code_for_query(isset($filter_values['lang']) ? $filter_values['lang'] : ''),
        );

        if (!empty($filter_values['cat'])) {
            $pagination_query_args['cat'] = absint($filter_values['cat']);
        }

        if (!empty($filter_values['year'])) {
            $pagination_query_args['year'] = absint($filter_values['year']);
        }

        if (!empty($filter_values['month'])) {
            $pagination_query_args['month'] = absint($filter_values['month']);
        }

        if (!empty($filter_values['s'])) {
            $pagination_query_args['s'] = sanitize_text_field(wp_unslash($filter_values['s']));
        }

        $pagination_base = add_query_arg($pagination_query_args, get_permalink($page_id));
        $pagination_base = add_query_arg('paged', '%#%', $pagination_base);

        $pagination_info_cn = sprintf(__('共 %d 頁・目前第 %d 頁', 'revamppage'), (int) $pa_query->max_num_pages, (int) $paged);
        $pagination_info_en = sprintf('Page %d of %d', (int) $paged, (int) $pa_query->max_num_pages);

        $pagination_info_html = '<div class="pa-pagination-info" data-cn="' . esc_attr($pagination_info_cn) . '" data-en="' . esc_attr($pagination_info_en) . '">' . esc_html($pagination_info_cn) . '</div>';

        $pagination_html = '<div class="pa-pagination">' . $pagination_info_html . paginate_links(array(
            'base' => $pagination_base,
            'format' => '',
            'current' => $paged,
            'total' => (int) $pa_query->max_num_pages,
            'prev_text' => '«',
            'next_text' => '»',
            'mid_size' => 2,
            'end_size' => 1,
        )) . '</div>';
    }

    return array(
        'grid_html' => $grid_html,
        'pagination_html' => $pagination_html,
        'pagination_info_html' => $pagination_info_html,
        'max_num_pages' => (int) $pa_query->max_num_pages,
        'found_posts' => (int) $pa_query->found_posts,
        'paged' => (int) $paged,
    );
}

function revamppage_handle_past_activities_filter()
{
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'revamppage_past_activities')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        wp_die();
    }

    $page_id = isset($_POST['page_id']) ? absint($_POST['page_id']) : 0;
    $paged = isset($_POST['paged']) ? max(1, absint($_POST['paged'])) : 1;

    $filter_values = array(
        'cat' => isset($_POST['cat']) ? sanitize_text_field(wp_unslash($_POST['cat'])) : '',
        'year' => isset($_POST['year']) ? sanitize_text_field(wp_unslash($_POST['year'])) : '',
        'month' => isset($_POST['month']) ? sanitize_text_field(wp_unslash($_POST['month'])) : '',
        's' => isset($_POST['s']) ? sanitize_text_field(wp_unslash($_POST['s'])) : '',
        'lang' => isset($_POST['lang']) ? sanitize_text_field(wp_unslash($_POST['lang'])) : '',
    );

    $pa_query = new WP_Query(revamppage_build_past_activities_query_args($page_id, $paged, 8, $filter_values));
    $markup = revamppage_render_past_activities_markup($pa_query, $paged, $page_id, $filter_values);

    wp_send_json_success(array(
        'grid_html' => $markup['grid_html'],
        'pagination_html' => $markup['pagination_html'],
        'max_num_pages' => $markup['max_num_pages'],
        'found_posts' => $markup['found_posts'],
        'paged' => $markup['paged'],
    ));
    wp_die();
}
add_action('wp_ajax_revamppage_filter_past_activities', 'revamppage_handle_past_activities_filter');
add_action('wp_ajax_nopriv_revamppage_filter_past_activities', 'revamppage_handle_past_activities_filter');

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