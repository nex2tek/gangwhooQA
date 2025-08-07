<?php

function nex2tek_echo($text) {
    echo function_exists('pll_e') ? pll_e($text) : $text;
}
function nex2tek_text($text) {
    return function_exists('pll__') ? pll__($text) : $text;
}

function get_current_lang() {
    if (function_exists('pll_current_language')) {
        return pll_current_language();
    }
    return substr(get_locale(), 0, 2); // fallback: vi_VN => vi
}

add_action('init', function () {
    if (function_exists('pll_register_string')) {
        pll_register_string('nex2tek_qa_question_title', 'ĐẶT CÂU HỎI', 'Nex2tek QA');
        pll_register_string('nex2tek_qa_question_note', 'Quý khách vui lòng điền đầy đủ thông tin bên dưới', 'Nex2tek QA');
        pll_register_string('nex2tek_qa_shortcode_question_view_title', 'Câu hỏi được xem nhiều nhất', 'Nex2tek QA');
        pll_register_string('nex2tek_qa_view_text', 'lượt xem', 'Nex2tek QA');
        pll_register_string('nex2tek_qa_no_question', 'Không có câu hỏi nào', 'Nex2tek QA');
        pll_register_string('nex2tek_qa_doctor_cta', 'Bác sĩ tư vấn', 'Nex2tek QA');
        pll_register_string('nex2tek_qa_view_more', 'Xem thêm', 'Nex2tek QA');
        pll_register_string('nex2tek_qa_view_less', 'Thu gọn', 'Nex2tek QA');
        pll_register_string('nex2tek_qa_question_pending_text', 'Câu hỏi tương tự đang chờ duyệt', 'Nex2tek QA');
        pll_register_string('nex2tek_qa_cannot_connect', 'Không thể kết nối xác minh bảo mật', 'Nex2tek QA');
        pll_register_string('nex2tek_qa_cannot_verify', 'Xác minh bảo mật thất bại. Vui lòng thử lại', 'Nex2tek QA');
        pll_register_string('nex2tek_qa_invalid_token', 'Token không hợp lệ hoặc đã hết hạn', 'Nex2tek QA');
        pll_register_string('nex2tek_qa_send_question_success', 'Câu hỏi của bạn đã được gửi thành công', 'Nex2tek QA');
        pll_register_string('nex2tek_qa_send_question_fail', 'Gửi câu hỏi thất bại', 'Nex2tek QA');
        pll_register_string('nex2tek_qa_question_content', 'Nội dung câu hỏi', 'Nex2tek QA');
        pll_register_string('nex2tek_qa_question_name', 'Tên của bạn', 'Nex2tek QA');
        pll_register_string('nex2tek_qa_question_phone', 'Số điện thoại', 'Nex2tek QA');
        pll_register_string('nex2tek_qa_question_anonymous','Người ẩn danh','Nex2tek QA');
        pll_register_string('nex2tek_qa_question_most_comment_title', 'Câu hỏi bình luận nhiều nhất', 'Nex2tek QA');
        pll_register_string('nex2tek_qa_comment', 'bình luận', 'Nex2tek QA');
        pll_register_string('nex2tek_qa_reply','Trả lời', 'Nex2tek QA');
        pll_register_string('nex2tek_qa_invalid_verify','Xác minh bảo mật không hợp lệ', 'Nex2tek QA');
        pll_register_string('nex2tek_qa_question_have','Có', 'Nex2tek QA');
        pll_register_string('nex2tek_qa_question_topic','câu hỏi cho chủ đề này', 'Nex2tek QA');
        pll_register_string('nex2tek_qa_choose_doctor','Chọn bác sĩ', 'Nex2tek QA');
        pll_register_string('nex2tek_qa_question_category','Chuyên mục', 'Nex2tek QA');
    }
});