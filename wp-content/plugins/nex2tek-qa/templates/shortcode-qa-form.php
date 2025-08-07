<?php if (!defined('ABSPATH')) exit; 

$success = false;
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['qa_question'])) {
    //Check nonce
    if (!isset($_POST['qa_nonce']) || !wp_verify_nonce($_POST['qa_nonce'], 'qa_submit_form')) {
        $error = __('Token không hợp lệ hoặc đã hết hạn.', 'nex2tek-qa');
    } else {
        //Sanitize input
        $question_content = sanitize_textarea_field($_POST['qa_question']);
        $name  = sanitize_text_field($_POST['qa_name']);
        $phone = sanitize_text_field($_POST['qa_phone']);
        $email = sanitize_email($_POST['qa_email']);

        $meta_data = [
            'qa_name'  => $name,
            'qa_phone' => $phone,
            'qa_email' => $email,
        ];

        // Step 3: Check duplicate question
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
            //Insert question post
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
                <h3>ĐẶT CÂU HỎI</h3>
                <p>Quý khách vui lòng điền đầy đủ thông tin bên dưới</p>
                <?php if ($success): ?>
                    <p class="qa-success qa-alert qa-alert-success"><?php _e('Câu hỏi của bạn đã được gửi thành công!', 'nex2tek-qa'); ?></p>
                <?php elseif (!empty($error)): ?>
                    <p class="qa-error qa-alert qa-alert-error"><?php echo esc_html($error); ?></p>
                <?php endif; ?>

                <form method="post" class="qa-form">
                    <?php wp_nonce_field('qa_submit_form', 'qa_nonce'); ?>
                    <label for="qa_question" class="form-label fw-semibold">
                        Nội dung câu hỏi <span class="qa-text-danger">*</span>
                    </label>
                    <textarea name="qa_question" id="qa_question" rows="4" required placeholder="Nhập nội dung câu hỏi..."></textarea>

                    <input type="text" name="qa_name" id="qa_name" placeholder="Tên của bạn" required>
                    <input type="tel" name="qa_phone" id="qa_phone" placeholder="Điện thoại" required>
                    <input type="email" name="qa_email" id="qa_email" placeholder="Email" required>

                    <div class="text-center mt-4 btn-group">
                        <button type="submit">ĐẶT CÂU HỎI</button>
                    </div>
                </form>
            </div>
            <?php echo do_shortcode('[nex2tek_qa_doctor_statistic]'); ?>
        </div>

        <!-- Sidebar right -->
        <div class="qa-col qa-sidebar-right">
            <?php echo do_shortcode('[nex2tek_qa_question_statistic]'); ?>
        </div>
    </div>
</div>