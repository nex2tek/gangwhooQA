<?php if (!defined('ABSPATH')) exit;

$success = false;
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['qa_question'])) {

    // --- Step 1: Check Cloudflare Turnstile ---
    $turnstile_response = $_POST['cf-turnstile-response'] ?? '';
    $secret_key = get_option('nex2tek_qa_secretkey', '');

    if (empty($turnstile_response)) {
        $error = __('Xác minh bảo mật không hợp lệ.', 'nex2tek-qa');
    } else {
        $verify_url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
        $response = wp_remote_post($verify_url, [
            'body' => [
                'secret'   => $secret_key,
                'response' => $turnstile_response,
                'remoteip' => $_SERVER['REMOTE_ADDR'],
            ],
        ]);

        if (is_wp_error($response)) {
            $error = __('Không thể kết nối xác minh bảo mật.', 'nex2tek-qa');
        } else {
            $response_body = wp_remote_retrieve_body($response);
            $result = json_decode($response_body, true);

            if (empty($result['success'])) {
                $error = __('Xác minh bảo mật thất bại. Vui lòng thử lại.', 'nex2tek-qa');
            }
        }
    }

    // --- Step 2: Check Nonce ---
    if (empty($error)) {
        if (!isset($_POST['qa_nonce']) || !wp_verify_nonce($_POST['qa_nonce'], 'qa_submit_form')) {
            $error = __('Token không hợp lệ hoặc đã hết hạn.', 'nex2tek-qa');
        }
    }

    // --- Step 3: Process data if there are no errors ---
    if (empty($error)) {
        // Sanitize inputs
        $question_content = sanitize_textarea_field($_POST['qa_question']);
        $name  = sanitize_text_field($_POST['qa_name']);
        $phone = sanitize_text_field($_POST['qa_phone']);
        $email = sanitize_email($_POST['qa_email']);

        $meta_data = [
            'qa_name'  => $name,
            'qa_phone' => $phone,
            'qa_email' => $email,
        ];

        // Step 4: Check duplicate questions
        $duplicate = get_posts([
            'post_type'   => 'question',
            'post_status' => 'pending',
            's'           => $question_content,
            'meta_query'  => [[
                'key'   => 'qa_email',
                'value' => $email,
            ]],
            'numberposts' => 1,
            'fields'      => 'ids',
        ]);

        if ($duplicate) {
            $error = __('Câu hỏi tương tự đang chờ duyệt.', 'nex2tek-qa');
        } else {
            // Insert post
            $post_id = wp_insert_post([
                'post_type'    => 'question',
                'post_title'   => wp_trim_words($question_content, 10, '...'),
                'post_content' => $question_content,
                'post_status'  => 'pending',
                'meta_input'   => $meta_data,
            ]);

            if ($post_id) {
                $success = true;
            } else {
                $error = __('Gửi câu hỏi thất bại. Vui lòng thử lại sau.', 'nex2tek-qa');
            }
        }
    }
}
?>

<div class="qa-container">
    <div class="qa-row">
        <!-- Sidebar left -->
        <div class="qa-col qa-sidebar-left">
            <?php echo do_shortcode('[nex2tek_qa_question_category]'); ?>
        </div>

        <!-- Form question -->
        <div class="qa-col qa-main-form">
            <div class="qa-form-wrapper">
                <h3><?php _e('ĐẶT CÂU HỎI', 'nex2tek-qa'); ?></h3>
                <p><?php _e('Quý khách vui lòng điền đầy đủ thông tin bên dưới', 'nex2tek-qa'); ?></p>
                <?php if ($success): ?>
                    <p class="qa-success qa-alert qa-alert-success"><?php _e('Câu hỏi của bạn đã được gửi thành công!', 'nex2tek-qa'); ?></p>
                <?php elseif (!empty($error)): ?>
                    <p class="qa-error qa-alert qa-alert-danger"><?php echo esc_html($error); ?></p>
                <?php endif; ?>

                <form method="post" class="qa-form">
                    <?php wp_nonce_field('qa_submit_form', 'qa_nonce'); ?>
                    <textarea name="qa_question" id="qa_question" rows="4" required placeholder="<?php _e('Nội dung câu hỏi', 'nex2tek-qa'); ?>"></textarea>
                    <input type="text" name="qa_name" id="qa_name" placeholder="Tên của bạn*" required>
                    <input type="tel" name="qa_phone" id="qa_phone" placeholder="Điện thoại*" required>
                    <input type="email" name="qa_email" id="qa_email" placeholder="Email*" required>
                    <div class="cf-turnstile" data-sitekey="<?php echo get_option('nex2tek_qa_sitekey', ''); ?>"></div>
                    <br>
                    <div class="text-center mt-4 btn-group">
                        <button type="submit"><?php _e('ĐẶT CÂU HỎI', 'nex2tek-qa'); ?></button>
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