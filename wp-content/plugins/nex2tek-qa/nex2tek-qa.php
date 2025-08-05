<?php
/*
Plugin Name: Q&A Plugin
Description: Plugin hỏi đáp bác sĩ cho WordPress.
Version: 1.0
Author: Nex2Tek
Text Domain: nex2tek-qa
*/

class Nex2Tek_QA {

    public function __construct() {
        add_action('init', array($this, 'register_post_types'));
        add_action('add_meta_boxes', array($this, 'add_question_meta_box'));
        add_action('save_post_question', array($this, 'save_question_meta'));
        add_shortcode('nex2tek_qa_list', array($this, 'qa_list_shortcode'));
        add_shortcode('nex2tek_qa_form', array($this, 'qa_form_shortcode'));
        add_filter('template_include', array($this, 'override_templates'));

    }

    public function register_post_types() {
        // Câu hỏi
        register_post_type('question', [
            'label' => __('Câu hỏi', 'nex2tek-qa'),
            'public' => true,
            'supports' => ['title', 'editor'],
            'has_archive' => true,
            'show_in_rest' => true,
            'rewrite' => ['slug' => 'cau-hoi'],
            'menu_icon' => 'dashicons-editor-help',
        ]);

        // Bác sĩ
        register_post_type('doctor', [
            'label' => __('Bác sĩ', 'nex2tek-qa'),
            'public' => true,
            'supports' => ['title', 'editor', 'thumbnail'],
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-groups',
        ]);

        // Taxonomy: Chuyên mục
        register_taxonomy('question_category', 'question', [
            'label' => __('Chuyên mục câu hỏi', 'nex2tek-qa'),
            'hierarchical' => true,
            'show_in_rest' => true,
        ]);
    }

    public function add_question_meta_box() {
        add_meta_box(
            'question_meta',
            __('Chi tiết câu hỏi', 'nex2tek-qa'),
            array($this, 'render_question_meta_box'),
            'question',
            'normal',
            'high'
        );
    }

    public function render_question_meta_box($post) {
        $doctors = get_posts(['post_type' => 'doctor', 'posts_per_page' => -1]);
        $selected_doctor = get_post_meta($post->ID, '_select_doctor', true);
        $answer = get_post_meta($post->ID, '_answer', true);

        echo '<p><label>' . __('Chọn bác sĩ:', 'nex2tek-qa') . '</label><br>';
        echo '<select name="select_doctor">';
        echo '<option value="">' . __('-- Chọn bác sĩ --', 'nex2tek-qa') . '</option>';
        foreach ($doctors as $doctor) {
            $selected = ($selected_doctor == $doctor->ID) ? 'selected' : '';
            echo "<option value='{$doctor->ID}' $selected>{$doctor->post_title}</option>";
        }
        echo '</select></p>';

        echo '<p><label>' . __('Trả lời:', 'nex2tek-qa') . '</label></p>';
        wp_editor($answer, 'answer', [
            'textarea_name' => 'answer',
            'textarea_rows' => 6,
        ]);
    }

    public function save_question_meta($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        if (isset($_POST['select_doctor'])) {
            update_post_meta($post_id, '_select_doctor', intval($_POST['select_doctor']));
        }

        if (isset($_POST['answer'])) {
            update_post_meta($post_id, '_answer', wp_kses_post($_POST['answer']));
        }
    }

    public function qa_list_shortcode() {
        ob_start();
        $query = new WP_Query([
            'post_type' => 'question',
            'post_status' => 'publish',
            'posts_per_page' => 10,
        ]);

        if ($query->have_posts()) {
            echo '<ul class="qa-list">';
            while ($query->have_posts()) {
                $query->the_post();
                $answer = get_post_meta(get_the_ID(), '_answer', true);
                echo '<li>';
                echo '<h3>' . get_the_title() . '</h3>';
                echo '<p>' . get_the_excerpt() . '</p>';
                if ($answer) {
                    echo '<div class="qa-answer"><strong>' . __('Trả lời:', 'nex2tek-qa') . '</strong><br>' . wpautop($answer) . '</div>';
                }
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>' . __('Không có câu hỏi nào.', 'nex2tek-qa') . '</p>';
        }

        wp_reset_postdata();
        return ob_get_clean();
    }

    public function qa_form_shortcode() {
        ob_start();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['qa_question'])) {
            $question_content = sanitize_text_field($_POST['qa_question']);
            $post_id = wp_insert_post([
                'post_type' => 'question',
                'post_title' => wp_trim_words($question_content, 10),
                'post_content' => $question_content,
                'post_status' => 'pending',
            ]);
            if ($post_id) {
                echo '<p>' . __('Câu hỏi của bạn đã được gửi thành công.', 'nex2tek-qa') . '</p>';
            }
        }

        ?>
        <form method="post">
            <p>
                <label><?php _e('Câu hỏi của bạn', 'nex2tek-qa'); ?></label><br>
                <textarea name="qa_question" required rows="5" cols="50"></textarea>
            </p>
            <p><button type="submit"><?php _e('Gửi câu hỏi', 'nex2tek-qa'); ?></button></p>
        </form>
        <?php

        return ob_get_clean();
    }

    public function override_templates($template) {
        // Giao diện chi tiết câu hỏi
        if (is_singular('question')) {
            $custom_template = plugin_dir_path(__FILE__) . 'templates/single-question.php';
            if (file_exists($custom_template)) return $custom_template;
        }

        // Giao diện danh sách câu hỏi
        if (is_post_type_archive('question')) {
            $custom_template = plugin_dir_path(__FILE__) . 'templates/archive-question.php';
            if (file_exists($custom_template)) return $custom_template;
        }

        // Giao diện chi tiết bác sĩ
        if (is_singular('doctor')) {
            $custom_template = plugin_dir_path(__FILE__) . 'templates/single-doctor.php';
            if (file_exists($custom_template)) return $custom_template;
        }

        // Giao diện category của câu hỏi
        if (is_tax('question_category')) {
            $custom_template = plugin_dir_path(__FILE__) . 'templates/taxonomy-question_category.php';
            if (file_exists($custom_template)) return $custom_template;
        }

        return $template;
    }
   
}

// Khởi tạo class
new Nex2Tek_QA();

// Hook tạo page khi kích hoạt
register_activation_hook(__FILE__, 'nex2tek_qa_create_pages');
function nex2tek_qa_create_pages() {
    if (!get_page_by_path('cau-hoi')) {
        wp_insert_post([
            'post_title'   => 'Câu hỏi',
            'post_name'    => 'cau-hoi',
            'post_content' => '[nex2tek_qa_list]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ]);
    }

    if (!get_page_by_path('gui-cau-hoi')) {
        wp_insert_post([
            'post_title'   => 'Gửi câu hỏi',
            'post_name'    => 'gui-cau-hoi',
            'post_content' => '[nex2tek_qa_form]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ]);
    }
}
