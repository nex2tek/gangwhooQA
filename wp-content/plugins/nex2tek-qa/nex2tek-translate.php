<?php

function nex2tek_echo($text) {
    echo function_exists('pll_e') ? pll_e($text) : $text;
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

        
    }
});