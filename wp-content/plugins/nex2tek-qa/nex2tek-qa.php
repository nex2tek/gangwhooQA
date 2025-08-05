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
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
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
            'pll' => true,
            'supports' => ['title', 'editor', 'custom-fields', 'comments'],
        ]);

        // Bác sĩ
        register_post_type('doctor', [
            'label' => __('Bác sĩ', 'nex2tek-qa'),
            'public' => true,
            'supports' => ['title', 'editor', 'thumbnail'],
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-groups',
            'pll' => true,
            'supports' => ['title', 'editor', 'custom-fields'],
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
            $question_content = sanitize_textarea_field($_POST['qa_question']);
            $name    = sanitize_text_field($_POST['qa_name']);
            $phone   = sanitize_text_field($_POST['qa_phone']);
            $email   = sanitize_email($_POST['qa_email']);
    
            $meta_data = [
                'name'  => $name,
                'phone' => $phone,
                'email' => $email,
            ];
    
            $post_id = wp_insert_post([
                'post_type'    => 'question',
                'post_title'   => wp_trim_words($question_content, 10),
                'post_content' => $question_content,
                'post_status'  => 'pending',
                'meta_input'   => $meta_data,
            ]);
    
            if ($post_id) {
                echo '<div class="alert alert-success">' . __('Câu hỏi của bạn đã được gửi thành công.', 'nex2tek-qa') . '</div>';
            }
        }
        ?>
    
        <form method="post" class="qa-form mt-4">
            <div class="mb-3">
                <label for="qa_question" class="form-label"><?php _e('Nội dung câu hỏi', 'nex2tek-qa'); ?> *</label>
                <textarea name="qa_question" id="qa_question" class="form-control" rows="5" required></textarea>
            </div>
            <div class="mb-3">
                <label for="qa_name" class="form-label"><?php _e('Tên của bạn', 'nex2tek-qa'); ?></label>
                <input type="text" name="qa_name" id="qa_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="qa_phone" class="form-label"><?php _e('Điện thoại', 'nex2tek-qa'); ?></label>
                <input type="tel" name="qa_phone" id="qa_phone" class="form-control">
            </div>
            <div class="mb-3">
                <label for="qa_email" class="form-label"><?php _e('Email', 'nex2tek-qa'); ?></label>
                <input type="email" name="qa_email" id="qa_email" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary"><?php _e('Gửi câu hỏi', 'nex2tek-qa'); ?></button>
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

        if(is_page('gui-cau-hoi')) {
            $custom_template = plugin_dir_path(__FILE__) . 'templates/page-question.php';
            if (file_exists($custom_template)) return $custom_template;
        }
        // Giao diện category của câu hỏi
        if (is_tax('question_category')) {
            $custom_template = plugin_dir_path(__FILE__) . 'templates/taxonomy-question_category.php';
            if (file_exists($custom_template)) return $custom_template;
        }

        return $template;
    }

    public function enqueue_assets() {
        // Styles
        wp_enqueue_style('bootstrap-icon', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css', [], '1.13.1');
        wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css', [], '5.0.0');
        wp_enqueue_style('nex2tek-qa-style', plugin_dir_url(__FILE__) . 'assets/style.css', [], '1.0.0');

        // Scripts
        wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js', ['jquery'], '5.0.0', true);
        wp_enqueue_script('nex2tek-qa-script', plugin_dir_url(__FILE__) . 'assets/scripts.js', ['jquery'], '1.0.0', true);
    }
   
}

// Khởi tạo class
new Nex2Tek_QA();

// Hook tạo page khi kích hoạt
register_activation_hook(__FILE__, 'nex2tek_qa_create_pages');
function nex2tek_qa_create_pages() {
    $default_lang = function_exists('pll_default_language') ? pll_default_language() : 'vi';

    if (!get_page_by_path('gui-cau-hoi')) {
        $post_id = wp_insert_post([
            'post_title'   => 'Gửi câu hỏi',
            'post_name'    => 'gui-cau-hoi',
            'post_content' => '[nex2tek_qa_form]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ]);

        if (function_exists('pll_set_post_language')) {
            pll_set_post_language($post_id, $default_lang);
        }
    }
}

// Cho phép Polylang hỗ trợ CPT 'question', 'doctor'
add_filter('pll_get_post_types', function ($post_types, $is_translatable) {
    $post_types['question'] = 'question';
    $post_types['doctor'] = 'doctor';

    return $post_types;
}, 10, 2);

// Cho phép Polylang hỗ trợ taxonomy 'question_category'
add_filter('pll_get_taxonomies', function($taxonomies, $is_translatable) {
    $taxonomies['question_category'] = true;
    
    return $taxonomies;
}, 10, 2);