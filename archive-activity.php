<?php
/**
 * Archive template for Activity post type
 */
get_header();
?>

<script>
document.body.classList.add('is-activity-page');
</script>

<div class="kwycc-activity-page">
    <!-- Hero Section with Background -->
    <div class="activity-hero">
        <div class="activity-hero-bg">
            <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/activity_banner.png" alt="Activity" loading="lazy">
        </div>
    </div>

    <!-- Main Content -->
    <div class="activity-container">
        <!-- Filter Section -->
        <div class="activity-filters">
            <h2 class="activity-category-title">
                <?php
                // Get category from query param or default
                $category = isset($_GET['cat']) ? sanitize_text_field($_GET['cat']) : '技能訓練班';
                echo esc_html($category);
                ?>
            </h2>
            
            <!-- Sort Dropdown -->
            <div class="activity-sort-wrapper">
                <select id="activity-sort" class="activity-sort-dropdown" aria-label="排序方式">
                    <option value="popular">最受歡迎</option>
                    <option value="newest">最新發佈</option>
                    <option value="deadline">即將截止</option>
                </select>
            </div>
        </div>

        <!-- Activities List -->
        <div class="activity-list" id="activity-list">
            <?php
            // Get sort parameter
            $sort = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : 'popular';

            // Build query based on sort
            $order_by = 'meta_value_num';
            $meta_key = '_activity_popularity';

            switch ($sort) {
                case 'newest':
                    $order_by = 'date';
                    $meta_key = '';
                    break;
                case 'deadline':
                    $order_by = 'meta_value';
                    $meta_key = '_activity_deadline';
                    break;
                default: // popular
                    $order_by = 'meta_value_num';
                    $meta_key = '_activity_popularity';
            }

            $args = array(
                'post_type' => 'activity',
                'posts_per_page' => -1,
                'orderby' => $order_by,
                'order' => 'DESC',
            );

            // Add meta_key and meta_query only if needed
            if (!empty($meta_key)) {
                $args['meta_key'] = $meta_key;
            }

            // Only filter by future deadlines
            $args['meta_query'] = array(
                array(
                    'key' => '_activity_deadline',
                    'value' => current_time('Y-m-d'),
                    'compare' => '>=',
                    'type' => 'DATE'
                )
            );

            $query = new WP_Query($args);

            if ($query->have_posts()):
                while ($query->have_posts()):
                    $query->the_post();

                    $deadline = get_post_meta(get_the_ID(), '_activity_deadline', true);
                    $total_seats = (int) get_post_meta(get_the_ID(), '_activity_total_seats', true);
                    $booked_seats = (int) get_post_meta(get_the_ID(), '_activity_booked_seats', true);
                    $remaining_seats = max(0, $total_seats - $booked_seats);
                    $is_full = ($remaining_seats <= 0);
                    $activity_code = get_post_meta(get_the_ID(), '_activity_code', true);
                    $activity_location = get_post_meta(get_the_ID(), '_activity_location', true);
                    $activity_time = get_post_meta(get_the_ID(), '_activity_time', true);
                    $short_desc = get_post_meta(get_the_ID(), '_activity_short_desc', true);

                    $deadline_display = $deadline ? date('Y-m-d', strtotime($deadline)) : 'N/A';
                    ?>
                    
                    <div class="activity-card" data-post-id="<?php echo esc_attr(get_the_ID()); ?>">
                        <div class="activity-card-inner">
                            <!-- Image Section -->
                            <div class="activity-card-image">
                                <?php
                                if (has_post_thumbnail()) {
                                    the_post_thumbnail('large', array('alt' => esc_attr(get_the_title())));
                                } else {
                                    echo '<img src="' . esc_url(get_stylesheet_directory_uri() . '/images/placeholder.png') . '" alt="' . esc_attr(get_the_title()) . '">';
                                }
                                ?>
                            </div>

                            <!-- Content Section -->
                            <div class="activity-card-content">
                                <h3 class="activity-card-title"><?php the_title(); ?></h3>
                                
                                <!-- Date & Time -->
                                <div class="activity-card-meta">
                                    <div class="activity-meta-item">
                                        <span class="activity-meta-icon">📅</span>
                                        <span class="activity-meta-text"><?php echo esc_html($deadline ? date('Y-m-d (D)', strtotime($deadline)) : 'N/A'); ?></span>
                                    </div>
                                    <?php if (!empty($activity_time)): ?>
                                        <div class="activity-meta-item">
                                            <span class="activity-meta-icon">🕐</span>
                                            <span class="activity-meta-text"><?php echo esc_html($activity_time); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Activity Details Grid -->
                                <div class="activity-details-grid">
                                    <div class="activity-detail">
                                        <span class="activity-detail-label">活動:</span>
                                        <span class="activity-detail-value"><?php echo esc_html(get_the_title()); ?></span>
                                    </div>
                                    <?php if (!empty($activity_code)): ?>
                                        <div class="activity-detail">
                                            <span class="activity-detail-label">編號:</span>
                                            <span class="activity-detail-value"><?php echo esc_html($activity_code); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($activity_location)): ?>
                                        <div class="activity-detail">
                                            <span class="activity-detail-label">地點:</span>
                                            <span class="activity-detail-value"><?php echo esc_html($activity_location); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="activity-detail">
                                        <span class="activity-detail-label">名額:</span>
                                        <span class="activity-detail-value"><?php echo esc_html($remaining_seats); ?></span>
                                    </div>
                                    <div class="activity-detail">
                                        <span class="activity-detail-label">截止日期:</span>
                                        <span class="activity-detail-value"><?php echo esc_html($deadline_display); ?></span>
                                    </div>
                                </div>

                                <!-- Signup Button -->
                                <button class="activity-btn-signup" data-post-id="<?php echo esc_attr(get_the_ID()); ?>">
                                    <?php echo $is_full ? esc_html__('已額滿', 'revamppage') : esc_html__('報名', 'revamppage'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php
                endwhile;
                wp_reset_postdata();
            else:
                ?>
                <div class="no-activities-message">
                    <p><?php esc_html_e('No activities found.', 'revamppage'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Activity Signup Modal -->
<div id="activity-modal-overlay" class="activity-modal-overlay" style="display: none;"></div>
<div id="activity-signup-modal" class="activity-modal" style="display: none;">
    <div class="activity-modal-content">
        <button class="activity-modal-close">&times;</button>
        <div class="activity-modal-header">
            <div class="activity-modal-image">
                <img id="modal-activity-image" src="" alt="Activity">
            </div>
            <div class="activity-modal-info">
                <h3 id="modal-activity-title">Activity Title</h3>
                <div class="activity-modal-meta">
                    <div>
                        <span class="modal-meta-label">Date:</span>
                        <span id="modal-activity-date">Date</span>
                    </div>
                </div>
                <div class="activity-modal-details">
                    <div>
                        <span class="modal-detail-label">活動:</span>
                        <span id="modal-activity-name">Name</span>
                    </div>
                    <div>
                        <span class="modal-detail-label">地點:</span>
                        <span id="modal-activity-location">Location</span>
                    </div>
                    <div>
                        <span class="modal-detail-label">名額:</span>
                        <span id="modal-activity-seats">Seats</span>
                    </div>
                    <div>
                        <span class="modal-detail-label">截止日期:</span>
                        <span id="modal-activity-deadline">Deadline</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="activity-signup-form">
            <h4 style="color: #0f0b1a; margin: 0 0 20px 0;">報名表格</h4>
            <form id="activity-signup-form" class="activity-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="chinese_name">中文名字 <span style="color: red;">*</span></label>
                        <input type="text" id="chinese_name" name="chinese_name" required>
                    </div>
                    <div class="form-group">
                        <label for="english_name">English Name</label>
                        <input type="text" id="english_name" name="english_name">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">電話 <span style="color: red;">*</span></label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label for="id_number">身份証號碼 <span style="color: red;">*</span></label>
                        <input type="text" id="id_number" name="id_number" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="category">報名類別</label>
                        <input type="text" id="category" name="category">
                    </div>
                    <div class="form-group">
                        <label for="category_confirm">確認報名類別</label>
                        <input type="text" id="category_confirm" name="category_confirm">
                    </div>
                </div>
                <input type="hidden" name="activity_nonce" value="<?php echo wp_create_nonce('activity_signup_nonce'); ?>">
                <div class="form-actions">
                    <button type="button" id="modal-cancel-btn" class="form-btn form-btn-cancel">取消</button>
                    <button type="submit" class="form-btn form-btn-submit">報名</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php get_footer(); ?>