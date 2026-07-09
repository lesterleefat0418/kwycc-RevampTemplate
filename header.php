<?php
    if (!defined('ABSPATH'))
        exit;
    ?><!doctype html>
    <html <?php language_attributes(); ?>>
    <head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
    </head>
    <body <?php body_class(); ?>>

    <?php
    // Helper: return array of WP object IDs referenced by a menu assigned to $theme_location
    if (!function_exists('revamppage_get_menu_page_ids')) {
        function revamppage_get_menu_page_ids(string $theme_location): array
        {
            $ids = array();
            $locations = get_nav_menu_locations();
            if (empty($locations) || !isset($locations[$theme_location])) {
                return $ids;
            }

            $menu_obj = wp_get_nav_menu_object($locations[$theme_location]);
            if (!$menu_obj) {
                return $ids;
            }

            $items = wp_get_nav_menu_items($menu_obj->term_id);
            if (empty($items)) {
                return $ids;
            }

            foreach ($items as $item) {
                // When a menu item points to a WP object (page/post/etc.) object_id holds that ID.
                if (!empty($item->object_id)) {
                    $ids[] = intval($item->object_id);
                }
            }

            return array_values(array_unique($ids));
        }
    }

    $menu_zh_page_ids = revamppage_get_menu_page_ids('primary-menu-zh');
    $menu_en_page_ids = revamppage_get_menu_page_ids('primary-menu-en');
    ?>

    <header class="kwycc-site-header">
      <div class="site-left">
        <?php
        if (function_exists('the_custom_logo') && has_custom_logo()) {
            the_custom_logo();
        } else {
            $site_icon_full = '';
            $site_icon_id = get_option('site_icon');
            if ($site_icon_id) {
                $full = wp_get_attachment_image_src($site_icon_id, 'full');
                if (!empty($full[0])) {
                    $site_icon_full = $full[0];
                }
            }

            if (empty($site_icon_full) && function_exists('get_site_icon_url')) {
                $maybe = get_site_icon_url();
                if ($maybe) {
                    $site_icon_full = $maybe;
                }
            }

            if (empty($site_icon_full)) {
                $site_icon_full = get_stylesheet_directory_uri() . '/assets/images/site-logo-original.png';
            }
            ?>
            <a class="site-logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
              <img src="<?php echo esc_url($site_icon_full); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" class="site-logo-img">
            </a>
        <?php } ?>
      </div>

      <div class="site-right">
              <?php
              // Determine activities archive / registration landing URL
              $activity_register_url = home_url('/activity/');  // Changed: Use /activity/ as primary fallback
          
              if (function_exists('get_post_type_archive_link')) {
                  $archive_link = get_post_type_archive_link('activity');
                  if (!empty($archive_link)) {
                      $activity_register_url = $archive_link;
                  }
              }
              ?>
        <!-- 報名活動 CTA (顯示於語言切換左側) -->
        <!--<a href="<?php echo esc_url($activity_register_url); ?>" class="kwycc-btn kwycc-cta" aria-label="<?php esc_attr_e('報名活動', 'revamppage'); ?>">
            <span class="btn-label">報名活動</span>
            <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/participate_btn.png'); ?>" 
                 alt="<?php esc_attr_e('報名活動', 'revamppage'); ?>" 
                 class="btn-icon participate-btn"/>
        </a>-->

        <!-- Language toggle -->
        <div class="kwycc-lang-wrapper">
          <button id="kwycc-lang-toggle" class="kwycc-btn kwycc-lang" aria-expanded="false" aria-label="<?php esc_attr_e('Toggle language', 'revamppage'); ?>">
            <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/globe.png'); ?>" alt="" class="btn-icon">
            <span class="btn-text">ç¹</span>
          </button>
          <nav id="kwycc-lang-menu" class="kwycc-dropdown-menu kwycc-lang-menu" aria-label="<?php esc_attr_e('Language selection', 'revamppage'); ?>">
            <a href="#" data-lang="zh" class="lang-link">中文</a>
            <a href="#" data-lang="en" class="lang-link">English</a>
          </nav>
        </div>

        <!-- Menu toggle -->
        <div class="kwycc-menu-wrapper">
          <button id="kwycc-menu-toggle" class="kwycc-btn kwycc-menu" aria-expanded="false" aria-controls="kwycc-main-nav" aria-label="<?php esc_attr_e('Toggle menu', 'revamppage'); ?>">
            <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/menu.png'); ?>" alt="" class="btn-icon">
            <span class="btn-text"></span>
          </button>
          <!-- Main navigation menu - now as dropdown -->
            <nav id="kwycc-main-nav" class="kwycc-dropdown-menu kwycc-main-nav" aria-label="<?php esc_attr_e('Main menu', 'revamppage'); ?>" data-current-lang="zh"
                 data-menu-zh-ids="<?php echo esc_attr( wp_json_encode( $menu_zh_page_ids ) ); ?>"
                 data-menu-en-ids="<?php echo esc_attr( wp_json_encode( $menu_en_page_ids ) ); ?>">
            <!-- Chinese Menu -->
            <div class="kwycc-menu-lang kwycc-menu-lang-active" data-lang="zh">
              <?php
              wp_nav_menu(array(
                  'theme_location' => 'primary-menu-zh',
                  'container' => false,
                  'menu_class' => 'kwycc-menu-list',
                  'fallback_cb' => 'revamppage_menu_fallback',
                  'depth' => 2,
                  'echo' => true,
              ));
              ?>
            </div>

            <!-- English Menu -->
            <div class="kwycc-menu-lang" data-lang="en">
              <?php
              wp_nav_menu(array(
                  'theme_location' => 'primary-menu-en',
                  'container' => false,
                  'menu_class' => 'kwycc-menu-list',
                  'fallback_cb' => 'revamppage_menu_fallback',
                  'depth' => 2,
                  'echo' => true,
              ));
              ?>
            </div>
          </nav>
        </div>
      </div>
    </header>
    <!-- header-->
    <script>
        const nav = document.getElementById('kwycc-main-nav');
        const zhIds = JSON.parse(nav ? nav.dataset.menuZhIds || '[]' : '[]');
        const enIds = JSON.parse(nav ? nav.dataset.menuEnIds || '[]' : '[]');

        // derive current page id from body class (WP adds "page-id-123")
        const bodyClass = document.body ? document.body.className : '';
        const match = bodyClass.match(/page-id-(\d+)/);
        const currentPageId = match ? parseInt(match[1], 10) : null;

        console.log('ZH IDs:', zhIds);
        console.log('EN IDs:', enIds);
        console.log('Current page ID:', currentPageId);
    </script>