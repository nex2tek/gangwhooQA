<?php if (!defined('ABSPATH')) exit;

$success = false;
$error   = '';
$is_enabled_captcha = get_option('nex2tek_qa_enable_captcha', false);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['qa_question'])) {
    $turnstile_response = $_POST['cf-turnstile-response'] ?? '';
    // 1. verify Captcha
    if ($is_enabled_captcha && !nex2tek_verify_turnstile($turnstile_response)) {
        $error = nex2tek_text('Xác minh bảo mật thất bại. Vui lòng thử lại', 'nex2tek-qa');
    }

    // 2. Check Nonce
    if (empty($error) && !nex2tek_verify_nonce()) {
        $error = nex2tek_text('Token không hợp lệ hoặc đã hết hạn', 'nex2tek-qa');
    }

    // 3. Insert question
    if (empty($error)) {
        $post_id = nex2tek_insert_question($_POST);
        if ($post_id) {
            $success = true;
        } else {
            $error = nex2tek_text('Gửi câu hỏi thất bại', 'nex2tek-qa');
        }
    }
}
?>
<div class="qa-container">
    <?php nex2tek_breadcrumb(); ?>
    <div class="qa-row">
        <!-- Sidebar left -->
        <div class="qa-col qa-sidebar-left">
            <?php echo do_shortcode('[nex2tek_qa_question_category]'); ?>
        </div>

        <!-- Form question -->
        <div class="qa-col qa-main-form">
            <div class="qa-form-wrapper">
                <h3><?php nex2tek_echo('ĐẶT CÂU HỎI', 'nex2tek-qa'); ?></h3>
                <p><?php nex2tek_echo('Quý khách vui lòng điền đầy đủ thông tin bên dưới', 'nex2tek-qa'); ?></p>
                <?php if ($success): ?>
                    <p class="qa-success qa-alert qa-alert-success"><?php nex2tek_echo('Câu hỏi của bạn đã được gửi thành công', 'nex2tek-qa'); ?>!</p>
                <?php elseif (!empty($error)): ?>
                    <p class="qa-error qa-alert qa-alert-danger"><?php echo esc_html($error); ?></p>
                <?php endif; ?>

                <form method="post" class="qa-form">
                    <?php wp_nonce_field('qa_submit_form', 'qa_nonce'); ?>
                    <textarea name="qa_question" id="qa_question" rows="4" required placeholder="<?php nex2tek_echo('Nội dung câu hỏi', 'nex2tek-qa'); ?>"></textarea>
                    <input type="text" name="qa_name" id="qa_name" placeholder="<?php nex2tek_echo('Tên của bạn', 'nex2tek-qa'); ?>*" required>
                    <input type="tel" name="qa_phone" id="qa_phone" placeholder="<?php nex2tek_echo('Số điện thoại', 'nex2tek-qa'); ?>*" required>
                    <input type="email" name="qa_email" id="qa_email" placeholder="<?php nex2tek_echo('Email', 'nex2tek-qa'); ?>*" required>
                    <?php if ($is_enabled_captcha): ?>
                        <div class="cf-turnstile" data-sitekey="<?php echo get_option('nex2tek_qa_sitekey', ''); ?>"></div>
                        <br>
                    <?php endif; ?>                    
                    <div class="text-center">
                        <button type="submit"><?php nex2tek_echo('ĐẶT CÂU HỎI', 'nex2tek-qa'); ?></button>
                    </div>
                </form>
            </div>
            <?php echo do_shortcode('[nex2tek_qa_doctor_list]'); ?>
        </div>

        <!-- Sidebar right -->
        <div class="qa-col qa-sidebar-right">
            <?php echo do_shortcode('[nex2tek_qa_question_view]'); ?>
            <?php echo do_shortcode('[nex2tek_qa_question_comment]'); ?>
        </div>
    </div>
</div>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>