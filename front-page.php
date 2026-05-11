<?php
/* Template Name: Revamp Front Page */
get_header();
?>

<div class="kwycc-hero">
   <h3>
      <span class="part1">暑期熱門</span><span class="part2">活動</span>
   </h3>

<?php
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
        <span class="part1">精彩</span><span class="part2">影片回顧</span>
      </h2>
      
      <div class="video-content">
        <div class="video-player" 
             style="background-image: url('<?php echo get_stylesheet_directory_uri(); ?>/images/video_base.png');
                    background-repeat: no-repeat;
                    background-position: center;
                    background-size: cover;">
          <div class="video-wrap">
            <iframe src="https://www.youtube.com/embed/UjiO08ywIU4?si=-yMu-aXSe0YhC0J-" title="活動影片" frameborder="0" allowfullscreen></iframe>
          </div>
        </div>

        <div class="video-info">
          <h3 class="video-heading">NBA 球星<br>Jimmy Butler 訪港活動</h3>
          <p class="video-description">
            NBA球星Jimmy Butler於2024年8月20日至21日再次到訪香港，與本地球迷面面。這次香港行程提他繼去年暑假後，再次訪港，與球迷互動見面及創造更多可能，掀起全城籃球熱潮。
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
        <span class="part1">會所架構</span><span class="part2">知多點</span>
      </h2>

      <div class="know-content"
           style="background-image: url('<?php echo get_stylesheet_directory_uri(); ?>/images/know-content_whole_base.png');
                    background-repeat: no-repeat;
                    background-position: center;
                    background-size: cover;">
        <div class="know-text">
          <p class="know-desc">西九龍護青委員會 現有二十八名會長及七名永遠名譽會長，均為熱心公益的社會賢達。除了會長外，委員會內亦有十多名當然委員，他們均為積極參與社區事務的專業人士，就區內的青少年問題向委員會提供意見並協助制定有關青少年活動的策略。</p>
          <p class="know-desc">當然委員包括三名區滅罪委員會主席 [ 油尖旺、九龍城及深水埗區 ] 、三名區校長會主席 [ 油尖旺、九龍城及深水埗區 ] ，以及九名分別來自不同政府或非政府部門的委員，包括警務處、社會福利署及四個非政府組織 [ 香港青年協會 
(九龍城)、 香港小童群益會 (深水埗)、香港遊樂場協會  (旺角) 及香港中華基督教青年會 (油尖) ]。</p>
        </div>
        <div class="know-image">
          <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/know_org_chart.png" alt="會所架構組織圖">
        </div>
      </div>
    </div>
  </section>

  <!-- Smarteen 必學小知識 -->
  <section class="kwycc-smarteen-section">
    <div class="container">
      <h2 class="section-title">
        <span class="part1">Smarteen</span><span class="part2">必學小知識</span>
      </h2>

      <div class="smarteen-content">
        <div class="smarteen-image">
          <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/smarteen-character.png" alt="Smarteen 角色">
        </div>
        <div class="smarteen-info">
          <div class="smarteen-card"
               style="background-image: url('<?php echo get_stylesheet_directory_uri(); ?>/images/know-content_base.png');
                background-repeat: no-repeat;
                background-position: center;
                background-size: cover;">
            <h3 class="smarteen-title">依托咪酯</h3>
            <p class="smarteen-desc">
              吸食依托咪酯的後果非常嚴重，例如吸食者會全身抽搐、神志不清、皮膚潰爛、身體不受控等類似「喪屍」的狀態，令吸食者的醜態盡露，尊嚴掃地，成為人生中難以磨滅的污點。
             </p>
            <p class="smarteen-desc">
              而警方增設的打擊依托咪酯24小時舉報熱線（號碼：6629 2966）亦已投入運作，以與市民一起合作打擊依托咪酯相關罪行。           
              另外，市民亦可透過即時通訊程式WhatsApp（號碼：6629 2966）或微信（帳戶：eto-report）作出舉報。
            </p>
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

          // Helper to get url by title (falls back to '#')
          function revamppage_menu_url($map, $title)
          {
              if (!empty($map[$title])) {
                  return esc_url($map[$title]);
              }
              return '#';
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