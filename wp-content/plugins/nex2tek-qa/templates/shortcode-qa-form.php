<?php if (!defined('ABSPATH')) exit; ?>

<div class="qa-container">
    <div class="qa-row">
        <!-- Sidebar chuyên mục -->
        <div class="qa-col qa-sidebar-left">
            <?php echo do_shortcode('[nex2tek_qa_question_category]'); ?>
        </div>

        <!-- Form câu hỏi -->
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
        </div>

        <!-- Sidebar thống kê -->
        <div class="qa-col qa-sidebar-right">
            <?php echo do_shortcode('[nex2tek_qa_question_statistic]'); ?>
        </div>
    </div>
</div>