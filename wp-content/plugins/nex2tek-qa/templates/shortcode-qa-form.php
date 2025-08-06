<?php if (!defined('ABSPATH')) exit; ?>

<div class="container mt-4">
    <div class="row">
        <!-- Sidebar chuyên mục -->
        <div class="col-lg-2">
            <?php echo do_shortcode('[nex2tek_qa_question_category]'); ?>
        </div>

        <!-- Form câu hỏi -->
        <div class="col-lg-7">
            <div class="qa-form-wrapper p-4 rounded-4 shadow-sm bg-white">
                <h3 class="fw-bold mb-2">ĐẶT CÂU HỎI</h3>
                <p class="text-muted mb-4">Quý khách vui lòng điền đầy đủ thông tin bên dưới</p>

                <?php if ($success): ?>
                    <div class="alert alert-success">Câu hỏi của bạn đã được gửi thành công.</div>
                <?php endif; ?>

                <form method="post" class="qa-form">
                    <div class="mb-3">
                        <label for="qa_question" class="form-label fw-semibold">
                            Nội dung câu hỏi <span class="text-danger">*</span>
                        </label>
                        <textarea name="qa_question" id="qa_question" class="form-control px-3 py-2" rows="4" required placeholder="Nhập nội dung câu hỏi..."></textarea>
                    </div>

                    <div class="mb-3">
                        <input type="text" name="qa_name" id="qa_name" class="form-control px-3 py-2" placeholder="Tên của bạn" required>
                    </div>

                    <div class="mb-3">
                        <input type="tel" name="qa_phone" id="qa_phone" class="form-control px-3 py-2" placeholder="Điện thoại" required>
                    </div>

                    <div class="mb-3">
                        <input type="email" name="qa_email" id="qa_email" class="form-control px-3 py-2" placeholder="Email" required>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn text-white px-4 py-2 fw-bold">
                            ĐẶT CÂU HỎI
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar thống kê -->
        <div class="col-lg-3">
            <?php echo do_shortcode('[nex2tek_qa_question_statistic]'); ?>
        </div>
    </div>
</div>
