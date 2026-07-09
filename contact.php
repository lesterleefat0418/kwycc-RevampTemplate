<?php
/* Template Name: 聯絡我們 (Contact) */
if (!defined('ABSPATH')) {
    exit;
}
get_header();

$contact_status = '';
if (isset($_GET['contact'])) {
    if ($_GET['contact'] === 'success') {
        $contact_status = 'success';
    } elseif ($_GET['contact'] === 'error') {
        $contact_status = 'error';
    }
}
?>
<section class="revamppage-contact container">
    <h2 class="section-title"><span class="part1">聯絡</span><span class="part2">我們</span></h2>

    <?php if ($contact_status === 'success'): ?>
        <div class="contact-notice contact-success" role="status">已成功發送，謝謝你的查詢。我們會盡快回覆你。</div>
    <?php elseif ($contact_status === 'error'): ?>
        <div class="contact-notice contact-error" role="alert">發送失敗，請稍後重試或使用其他聯絡方式。</div>
    <?php endif; ?>

    <div class="contact-grid">
        <form class="contact-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('revamppage_contact_nonce', 'revamppage_contact_nonce'); ?>
            <input type="hidden" name="action" value="revamppage_contact">

            <div class="form-group">
                <label for="contact_name">姓名</label>
                <input id="contact_name" name="contact_name" type="text" required>
            </div>

            <div class="form-group">
                <label for="contact_email">電郵</label>
                <input id="contact_email" name="contact_email" type="email" required>
            </div>

            <div class="form-group">
                <label for="contact_subject">主題</label>
                <input id="contact_subject" name="contact_subject" type="text" required>
            </div>

            <div class="form-group">
                <label for="contact_message">內容</label>
                <textarea id="contact_message" name="contact_message" rows="6" required></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">發送</button>
            </div>
        </form>

        <aside class="contact-info">
            <h3>聯絡資料</h3>
            <p>電話: 0123-456-789</p>
            <p>電郵: <a href="mailto:info@example.com">info@example.com</a></p>
            <p>地址: 香港九龍示範地址</p>
        </aside>
    </div>
</section>

<?php get_footer(); ?>