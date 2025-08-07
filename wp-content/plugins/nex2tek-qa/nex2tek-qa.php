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
        add_shortcode('nex2tek_qa_question_category', array($this, 'qa_question_category_shortcode'));
        add_shortcode('nex2tek_qa_question_view', array($this, 'qa_question_view_shortcode'));
        add_shortcode('nex2tek_qa_question_comment', array($this, 'qa_question_comment_shortcode'));
        add_shortcode('nex2tek_qa_doctor_list', array($this, 'qa_doctor_list_shortcode'));
        add_shortcode('nex2tek_qa_button_create_question', array($this, 'qa_button_create_question_shortcode'));
        add_action('add_meta_boxes', array($this, 'add_doctor_title_meta_box'));
        add_action('save_post_doctor', array($this, 'save_doctor_meta'));
        add_action('pre_get_posts', array($this, 'custom_post_query_question_category'));
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'add_settings_link']);
    }

    public function register_post_types() {
        // Q&A
        register_post_type('question', [
            'label' => __('Câu hỏi', 'nex2tek-qa'),
            'public' => true,
            'has_archive' => true,
            'show_in_rest' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-editor-help',
            'comment_status' => 'open',
            'rewrite' => [
                'slug' => 'cau-hoi',
                'with_front' => false
            ],
            'supports' => ['title', 'editor', 'custom-fields', 'comments','thumbnail'],
        ]);

        // Doctor
        register_post_type('doctor', [
            'label' => __('Bác sĩ', 'nex2tek-qa'),
            'public' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-groups',
            'rewrite' => [
                'slug' => 'bac-si',
                'with_front' => false
            ],
            'supports' => ['title', 'editor', 'custom-fields','thumbnail', 'excerpt'],
        ]);

        // Taxonomy
      register_taxonomy('question_category', 'question', [
            'label' => __('Chuyên mục câu hỏi', 'nex2tek-qa'),
            'public' => true,
            'hierarchical' => true,
            'show_ui' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
            'rewrite' => ['slug' => 'chuyen-muc-cau-hoi'],
            'labels' => [
                'name' => __('Chuyên mục câu hỏi', 'nex2tek-qa'),
                'singular_name' => __('Chuyên mục', 'nex2tek-qa'),
            ],
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

    public function add_doctor_title_meta_box() {
        add_meta_box(
            'doctor_title_meta',
            'Chức danh bác sĩ',
            function($post) {
                $value = get_post_meta($post->ID, 'doctor_title', true);
                echo '<input type="text" name="doctor_title" value="' . esc_attr($value) . '" class="widefat">';
            },
            'doctor',
            'normal',
            'default'
        );
    }

    public function render_question_meta_box($post) {
        $doctors = get_posts(['post_type' => 'doctor', 'posts_per_page' => -1]);
        $selected_doctor = get_post_meta($post->ID, '_select_doctor', true);
        $answer = get_post_meta($post->ID, '_answer', true);

        echo '<p><label>' . nex2tek_text('Chọn bác sĩ', 'nex2tek-qa') . ':</label><br>';
        echo '<select name="select_doctor">';
        echo '<option value="">--' . nex2tek_text('Chọn bác sĩ', 'nex2tek-qa') . '--</option>';
        foreach ($doctors as $doctor) {
            $selected = ($selected_doctor == $doctor->ID) ? 'selected' : '';
            echo "<option value='{$doctor->ID}' $selected>{$doctor->post_title}</option>";
        }
        echo '</select></p>';

        echo '<p><label>' . nex2tek_text('Trả lời', 'nex2tek-qa') . ':</label></p>';
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

    public function save_doctor_meta($post_id) {
        if (array_key_exists('doctor_title', $_POST)) {
            update_post_meta($post_id, 'doctor_title', sanitize_text_field($_POST['doctor_title']));
        }
    }

    public function qa_list_shortcode() {
        ob_start();
        $template_file = plugin_dir_path(__FILE__) . 'templates/shortcode-qa-list.php';
        if (file_exists($template_file)) {
            include $template_file;
        }
        return ob_get_clean();
    }


    public function qa_form_shortcode() {
        ob_start();
        // Load form template
        $template_file = plugin_dir_path(__FILE__) . 'templates/shortcode-qa-form.php';
        if (file_exists($template_file)) {
            include $template_file;
        }
        return ob_get_clean();
    }
   
    
    public function qa_question_category_shortcode() {
        // Get the list of terms belonging to the 'question_category' taxonomy
        ob_start();
        $template_file = plugin_dir_path(__FILE__) . 'templates/shortcode-question-category.php';
        if (file_exists($template_file)) {
            include $template_file;
        }
        return ob_get_clean();  
    }
    
    public function qa_question_view_shortcode() {
        ob_start();
        $template_file = plugin_dir_path(__FILE__) . 'templates/shortcode-question-view.php';
        if (file_exists($template_file)) {
            include $template_file;
        }
        return ob_get_clean();
    } 

    public function qa_question_comment_shortcode() {
        ob_start();
        $template_file = plugin_dir_path(__FILE__) . 'templates/shortcode-question-comment.php';
        if (file_exists($template_file)) {
            include $template_file;
        }
    
        return ob_get_clean();
    }    
    
    public function qa_doctor_list_shortcode() {
        ob_start();
        $template_file = plugin_dir_path(__FILE__) . 'templates/shortcode-doctor-list.php';
        if (file_exists($template_file)) {
            include $template_file;
        }
        return ob_get_clean();
    } 
    
    public function qa_button_create_question_shortcode() {
        ob_start();
        $template_file = plugin_dir_path(__FILE__) . 'templates/shortcode-button-create-question.php';
        if (file_exists($template_file)) {
            include $template_file;
        }
        return ob_get_clean();
    }
    private function get_translated_page_id_by_slug($slug) {
        $page = get_page_by_path($slug);
        if (!$page) return false;
        return function_exists('pll_get_post') ? pll_get_post($page->ID) : $page->ID;
    }

    public function override_templates($template) {
        // question detail UI

         $is_page_question      = $this->get_translated_page_id_by_slug('hoi-dap');
         $is_page_question_form  = $this->get_translated_page_id_by_slug('gui-cau-hoi');
        if (is_singular('question')) {
           
            $custom_template = plugin_dir_path(__FILE__) . 'templates/single-question.php';
            if (file_exists($custom_template)) return $custom_template;
        }
        // doctor detail UI
        if (is_singular('doctor')) {
            $custom_template = plugin_dir_path(__FILE__) . 'templates/single-doctor.php';
            if (file_exists($custom_template)) return $custom_template;
        }
        // question page UI
        if(is_page($is_page_question) || is_page($is_page_question_form)) {
            $custom_template = plugin_dir_path(__FILE__) . 'templates/page-question.php';
            if (file_exists($custom_template)) return $custom_template;
        }
        // question category page UI
        if (is_tax('question_category')) {
            $custom_template = plugin_dir_path(__FILE__) . 'templates/taxonomy-question_category.php';
            if (file_exists($custom_template)) return $custom_template;
        }

        return $template;
    }

    public function custom_post_query_question_category($query) {
        if (!is_admin() && $query->is_main_query() && is_tax('question_category')) {
         
            $query->set('posts_per_page', 12);
        }
        
    }

    public function enqueue_assets() {
        // Styles
        wp_enqueue_style('nex2tek-qa-style', plugin_dir_url(__FILE__) . 'assets/style.css', [], '1.0.0');
        wp_enqueue_style('nex2tek-question-detail-style', plugin_dir_url(__FILE__) . 'assets/question-detail.css', [], '1.0.0');

        // Scripts
        wp_enqueue_script('nex2tek-qa-script', plugin_dir_url(__FILE__) . 'assets/script.js', ['jquery'], '1.0.0', true);
    }

    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('options-general.php?page=nex2tek-qa-settings') . '">Settings</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
}

new Nex2Tek_QA();

// Hook create pages when plugin activated
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
    
    if (!get_page_by_path('hoi-dap')) {
        $post_id = wp_insert_post([
            'post_title'   => 'Hỏi Đáp',
            'post_name'    => 'hoi-dap',
            'post_content' => '[nex2tek_qa_list]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ]);

        if (function_exists('pll_set_post_language')) {
            pll_set_post_language($post_id, $default_lang);
        }
    }
}

include_once plugin_dir_path(__FILE__) . 'nex2tek-functions.php';
include_once plugin_dir_path(__FILE__) . 'nex2tek-translate.php';
