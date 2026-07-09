<?php
/* Template Name: Revamp Front Page */
get_header();
?>

<?php
// Helper to find a post/page by slug across common types
if (!function_exists('revamppage_get_post_by_slug')) {
    function revamppage_get_post_by_slug($slug)
    {
        if (!$slug)
            return null;
        // try page first, then post
        $p = get_page_by_path($slug, OBJECT, 'page');
        if ($p)
            return $p;
        return get_page_by_path($slug, OBJECT, 'post');
    }
}

// DOM helpers: extract inner HTML of element by class, or attribute of child element
if (!function_exists('revamppage_extract_inner_html_by_class')) {
    function revamppage_extract_inner_html_by_class($html, $className)
    {
        if (empty($html) || empty($className)) {
            return '';
        }

        libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        $wrapped = '<!doctype html><html><body>' . $html . '</body></html>';
        $loaded = $doc->loadHTML(mb_convert_encoding($wrapped, 'HTML-ENTITIES', 'UTF-8'));
        if (!$loaded) {
            libxml_clear_errors();
            return '';
        }

        $xpath = new DOMXPath($doc);
        $nodes = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' " . $className . " ')]");
        if ($nodes->length === 0) {
            libxml_clear_errors();
            return '';
        }

        $node = $nodes->item(0);
        $inner = '';
        foreach ($node->childNodes as $child) {
            $inner .= $doc->saveHTML($child);
        }

        libxml_clear_errors();
        return $inner;
    }
}

if (!function_exists('revamppage_extract_child_inner_html')) {
    // find element with className, then find first child tagName (optional) and return innerHTML
    function revamppage_extract_child_inner_html($html, $className, $childTag = null)
    {
        if (empty($html) || empty($className)) {
            return '';
        }

        libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        $wrapped = '<!doctype html><html><body>' . $html . '</body></html>';
        $loaded = $doc->loadHTML(mb_convert_encoding($wrapped, 'HTML-ENTITIES', 'UTF-8'));
        if (!$loaded) {
            libxml_clear_errors();
            return '';
        }

        $xpath = new DOMXPath($doc);
        $nodes = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' " . $className . " ')]");
        if ($nodes->length === 0) {
            libxml_clear_errors();
            return '';
        }

        $root = $nodes->item(0);

        if ($childTag) {
            $childNodes = $xpath->query(".//" . $childTag, $root);
            if ($childNodes->length === 0) {
                libxml_clear_errors();
                return '';
            }
            $target = $childNodes->item(0);
        } else {
            $target = $root;
        }

        $inner = '';
        foreach ($target->childNodes as $child) {
            $inner .= $doc->saveHTML($child);
        }

        libxml_clear_errors();
        return $inner;
    }
}

if (!function_exists('revamppage_extract_child_attribute')) {
    // find element with className, then find first child tagName and return attribute value
    function revamppage_extract_child_attribute($html, $className, $childTag, $attrName)
    {
        if (empty($html) || empty($className) || empty($childTag) || empty($attrName)) {
            return '';
        }

        libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        $wrapped = '<!doctype html><html><body>' . $html . '</body></html>';
        $loaded = $doc->loadHTML(mb_convert_encoding($wrapped, 'HTML-ENTITIES', 'UTF-8'));
        if (!$loaded) {
            libxml_clear_errors();
            return '';
        }

        $xpath = new DOMXPath($doc);
        $nodes = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' " . $className . " ')]");
        if ($nodes->length === 0) {
            libxml_clear_errors();
            return '';
        }

        $root = $nodes->item(0);
        $childNodes = $xpath->query(".//" . $childTag, $root);
        if ($childNodes->length === 0) {
            libxml_clear_errors();
            return '';
        }

        $value = $childNodes->item(0)->getAttribute($attrName);
        libxml_clear_errors();
        return $value;
    }
}

// current (Chinese) front page post
$post = get_queried_object(); // this template is used for the front page -> queried object is the front page
// prepare raw post content (unfiltered) so we can parse markup reliably
$raw = $post && !empty($post->post_content) ? $post->post_content : '';
//Extract summar title from the Chinese post
$summary_title_html = revamppage_extract_inner_html_by_class($raw, 'kwycc-hero');

// Extract video pieces from the Chinese post
$video_title_html = revamppage_extract_child_inner_html($raw, 'video-title', 'span');
$iframe_src = revamppage_extract_child_attribute($raw, 'kwycc-video', 'iframe', 'src');
$video_heading_html = revamppage_extract_child_inner_html($raw, 'video-heading'); // inner of h2/h3.video-title
$video_description_html = revamppage_extract_child_inner_html($raw, 'video-description');

// Extract know-section pieces
$know_title_html = revamppage_extract_child_inner_html($raw, 'section-title');
$know_text_html = revamppage_extract_child_inner_html($raw, 'know-text');
$know_image_html = revamppage_extract_child_attribute($raw, 'know-image', 'img', 'src');

// Extract smarteen section pieces
$smarteen_title_html = revamppage_extract_child_inner_html($raw, 'kwycc-smarteen');
$smarteen_image_html = revamppage_extract_child_attribute($raw, 'smarteen-image', 'img', 'src');

$smarteen_inner_title_html = revamppage_extract_inner_html_by_class($raw, 'smarteen-title');
$smarteen_inner_info_html = revamppage_extract_inner_html_by_class($raw, 'smarteen-info-desc');

// Prepare safe full-content fallbacks if needed
$html = $post && !empty($post->post_content) ? apply_filters('the_content', $post->post_content) : '';
?>

<div class="kwycc-hero">
<?php
    echo $summary_title_html;
// Query for activity posts
$args = array(
    'post_type' => 'activity',
    'posts_per_page' => 5,
    'orderby' => 'meta_value',
    'meta_key' => '_activity_deadline',
    'order' => 'ASC',
    'meta_query' => array(
        array(
            'key' => '_activity_deadline',
            'value' => current_time('Y-m-d'),
            'compare' => '>=',
            'type' => 'DATE'
        )
    )
);

$query = new WP_Query($args);
$total_posts = $query->found_posts;
$show_nav = $total_posts >= 5; // ✅ 檢查是否需要顯示 nav
?>

<!-- ✅ 新增容器包裝 scroll-wrap 和 nav 按鈕 -->
   <div class="kwycc-scroll-container<?php echo $show_nav ? '' : ' hide-nav'; ?>">
       <!-- 左箭頭按鈕 -->
       <button class="kwycc-scroll-nav kwycc-scroll-nav-left" aria-label="向左滾動" <?php echo !$show_nav ? 'disabled' : ''; ?>>
           &#10094;
       </button>

       <!-- Scroll wrap -->
       <div class="kwycc-scroll-wrap" aria-label="熱門活動橫向滾動">
           <div class="kwycc-scroll" id="kwycc-scroll">
               <?php
               $index = 0;

               if ($query->have_posts()):
                   while ($query->have_posts()):
                       $query->the_post();
                       // Get custom fields
                       $deadline = get_post_meta(get_the_ID(), '_activity_deadline', true);
                       $total_seats = (int) get_post_meta(get_the_ID(), '_activity_total_seats', true);
                       $booked_seats = (int) get_post_meta(get_the_ID(), '_activity_booked_seats', true);
                       $remaining_seats = max(0, $total_seats - $booked_seats);
                       $is_full = ($remaining_seats <= 0);

                       // Get registration page URL
                       $registration_url = get_post_meta(get_the_ID(), '_activity_registration_url', true);
                       if (empty($registration_url)) {
                           $registration_url = get_permalink();
                       }

                       $deadline_display = $deadline ? date('d/m/Y', strtotime($deadline)) : 'N/A';
                       ?>
                       
                       <article class="kwycc-card" data-index="<?php echo $index; ?>" role="article" aria-label="<?php echo esc_attr(get_the_title()); ?>" data-registration-url="<?php echo esc_attr($registration_url); ?>">
                           <a href="<?php echo esc_url($registration_url); ?>" draggable="false" class="card-link" aria-label="<?php echo esc_attr(get_the_title()); ?> - 報名">
                               <div class="card-media">
                                   <?php
                                   if (has_post_thumbnail()) {
                                       the_post_thumbnail('medium', array('alt' => esc_attr(get_the_title())));
                                   } else {
                                       echo '<img src="' . esc_url(get_stylesheet_directory_uri() . '/images/placeholder.png') . '" alt="' . esc_attr(get_the_title()) . '">';
                                   }
                                   ?>
                               </div>

                               <div class="card-content"
                                    style="background-image: url('<?php echo get_stylesheet_directory_uri(); ?>/images/card_bottom.png');
                                           background-repeat: no-repeat;
                                           background-position: center;
                                           background-size: cover;">
                                   <div class="card-info">
                                       <h4 class="card-title"><?php the_title(); ?></h4>
                                       <p class="card-deadline">截止: <?php echo esc_html($deadline_display); ?></p>
                                   </div>

                                   <div class="card-footer"
                                        style="background-image: url('<?php echo get_stylesheet_directory_uri(); ?>/images/full-status-Bg.png');
                                               background-repeat: no-repeat;
                                               background-position: center;
                                               background-size: contain;">
                                       <span class="card-status">尚餘名額</span>
                                       <?php if ($is_full): ?>
                                           <span class="card-status full">已滿名額</span>
                                       <?php else: ?>
                                           <span class="card-status seats"><?php echo esc_html($remaining_seats); ?>/<?php echo esc_html($total_seats); ?></span>
                                       <?php endif; ?>
                                   </div>
                               </div>
                           </a>
                       </article>

                       <?php
                       $index++;
                   endwhile;
                   wp_reset_postdata();
               else:
                   echo '<p style="color: #fff; text-align: center; padding: 20px;">暫時未有暑期活動</p>';
               endif;
               ?>
           </div>
       </div>

       <!-- 右箭頭按鈕 -->
       <button class="kwycc-scroll-nav kwycc-scroll-nav-right" aria-label="向右滾動" <?php echo !$show_nav ? 'disabled' : ''; ?>>
           &#10095;
       </button>
   </div>
</div>

  <!-- 精彩影片回顧 -->
  <div class="kwycc-video" id="kwycc-video">
    <div class="container">
      <h2 class="video-title">
        <?php
        echo $video_title_html;
        ?>
      </h2>
      
      <div class="video-content">
        <div class="video-player" 
             style="background-image: url('<?php echo get_stylesheet_directory_uri(); ?>/images/video_base.png');
                    background-repeat: no-repeat;
                    background-position: center;
                    background-size: cover;">
          <div class="video-wrap">
             <?php
             $iframe_src = esc_url($iframe_src);
             ?>
             <iframe src="<?php echo $iframe_src; ?>" title="活動影片" frameborder="0" allowfullscreen></iframe>
          </div>
        </div>

        <div class="video-info">
          <h3 class="video-heading">
            <?php
            echo $video_heading_html;
            ?>
          </h3>
          <p class="video-description">
            <?php
            echo $video_description_html;
            ?>
          </p>
          <a href="#" class="video-link" aria-label="查看更多">
            <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/caretRight.png" alt="查看更多">
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- 會所架構知多點 -->
  <section class="kwycc-know-section">
    <div class="container">
      <h2 class="section-title">
        <?php
        echo $know_title_html;
        ?>
      </h2>

      <div class="know-content"
           style="background-image: url('<?php echo get_stylesheet_directory_uri(); ?>/images/know-content_whole_base.png');
                    background-repeat: no-repeat;
                    background-position: center;
                    background-size: cover;">
        <div class="know-text">
            <?php
            echo $know_text_html;
            ?>
        </div>
        <div class="know-image">
          <img src="
          <?php
            echo $know_image_html;
          ?>">
        </div>
      </div>
    </div>
  </section>

  <!-- Smarteen 必學小知識 -->
  <section class="kwycc-smarteen-section">
    <div class="container">
      <h2 class="section-title">
          <?php
            echo $smarteen_title_html;
          ?>
      </h2>

      <div class="smarteen-content">
        <div class="smarteen-image">
          <img src="
          <?php
          echo $smarteen_image_html;
          ?>">
        </div>
        <div class="smarteen-info">
          <div class="smarteen-card"
               style="background-image: url('<?php echo get_stylesheet_directory_uri(); ?>/images/know-content_base.png');
                background-repeat: no-repeat;
                background-position: center;
                background-size: cover;">
            <h3 class="smarteen-title">          
                <?php
                    echo $smarteen_inner_title_html;
                ?>
            </h3>
            <div>                
                <?php
                    echo $smarteen_inner_info_html;
                ?>
              </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 打開我們的電子通訊 Newsletter -->
  <section class="kwycc-newsletter-section">
    <div class="container">
      <div class="newsletter-wrapper">
        <!-- Left side: Newsletter subscription -->
        <div class="newsletter-left">
          <h2 class="newsletter-title">訂閱我們的電子通訊</h2>
          
          <form class="newsletter-form">
            <div class="form-group">
              <input type="email" class="form-input" placeholder="請輸入你的電郵地址" required>
            </div>
            <div class="form-group">
              <input type="text" class="form-input" placeholder="請輸入你的名字" required>
            </div>
            <div class="form-group">
              <button type="submit" class="form-submit">發送</button>
            </div>
          </form>
        </div>

        <!-- Right side: Footer links (two parts) -->
        <div class="newsletter-right">
          <?php
          // Load the assigned primary menu (Chinese) so links come from Customizer menu items.
          $locations = get_nav_menu_locations();
          $menu_items = array();
          if (!empty($locations['primary-menu-zh'])) {
              $menu_items = wp_get_nav_menu_items($locations['primary-menu-zh']);
          }

          // Build quick lookup by exact title
          $menu_map = array();
          if (!empty($menu_items)) {
              foreach ($menu_items as $mi) {
                  $menu_map[trim($mi->title)] = $mi->url;
              }
          }

          // Helper to get url by title (menu -> slug -> exact-title via WP_Query)
          if (!function_exists('revamppage_menu_url')) {
              function revamppage_menu_url($map, $title)
              {
                  // 1) Exact menu item match
                  if (!empty($map[$title])) {
                      return esc_url($map[$title]);
                  }

                  // 2) Try common slug variants first (fast, non-deprecated)
                  $possible_slugs = array('contact', 'contact-us', '架構及宗旨', 'structure', 'about', 'about-structure');
                  foreach ($possible_slugs as $slug) {
                      $p = get_page_by_path($slug, OBJECT, 'page');
                      if ($p && !empty($p->ID)) {
                          return get_permalink($p->ID);
                      }
                  }

                  // 3) Search pages by title using WP_Query and match exact title (case-insensitive)
                  $q = new WP_Query(array(
                      'post_type'      => 'page',
                      'post_status'    => 'publish',
                      's'              => $title,
                      'posts_per_page' => 6,
                      'no_found_rows'  => true,
                  ));
                  if ($q->have_posts()) {
                      foreach ($q->posts as $p) {
                          if (mb_strtolower(trim($p->post_title)) === mb_strtolower(trim($title))) {
                              wp_reset_postdata();
                              return get_permalink($p->ID);
                          }
                      }
                      wp_reset_postdata();
                  }

                  // 4) final fallback
                  return '#';
              }
          }
          ?>

          <!-- Top part: Navigation links -->
          <div class="newsletter-top">
            <div class="newsletter-column">
              <a href="<?php echo revamppage_menu_url($menu_map, '主頁'); ?>" class="newsletter-link-title">主頁</a>
              <a href="<?php echo revamppage_menu_url($menu_map, '聯絡我們'); ?>" class="newsletter-column-link">聯絡我們</a>
            </div>
            <div class="newsletter-column">
              <a href="<?php echo revamppage_menu_url($menu_map, '架構及宗旨'); ?>" class="newsletter-link-title">架構及宗旨</a>
              <a href="<?php echo revamppage_menu_url($menu_map, '全年概覽'); ?>" class="newsletter-column-link">全年概覽</a>
              <a href="<?php echo revamppage_menu_url($menu_map, 'Smartteen透視'); ?>" class="newsletter-column-link">Smartteen透視</a>
            </div>
            <div class="newsletter-column">
              <a href="<?php echo revamppage_menu_url($menu_map, '活動預告'); ?>" class="newsletter-link-title">活動預告</a>
              <a href="<?php echo revamppage_menu_url($menu_map, '影片回顧'); ?>" class="newsletter-column-link">影片回顧</a>
              <a href="<?php echo revamppage_menu_url($menu_map, '活動報名'); ?>" class="newsletter-column-link">活動報名</a>
            </div>
          </div>

         <!-- Back to Top Button -->
        <a href="#" class="back-to-top" aria-label="返回頁首">
            <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/arrow.png" alt="返回頁首" loading="lazy">
        </a>



          <!-- Bottom part: Privacy & Copyright -->
          <div class="newsletter-bottom">
            <a href="<?php echo revamppage_menu_url($menu_map, '私隱條款'); ?>">私隱條款</a>
            <span class="separator">|</span>
            <a href="<?php echo revamppage_menu_url($menu_map, '重要告示'); ?>">重要告示</a>
            <p class="copyright">Copyright © 2026 西九龍護青委員會版權所有</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<?php get_footer(); ?>