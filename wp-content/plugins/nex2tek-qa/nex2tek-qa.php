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
        add_shortcode('nex2tek_qa_question_statistic', array($this, 'qa_question_statistic_shortcode'));
        
    }

    public function register_post_types() {
        // Câu hỏi
        register_post_type('question', [
            'label' => __('Câu hỏi', 'nex2tek-qa'),
            'public' => true,
            'has_archive' => true,
            'show_in_rest' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-editor-help',
            'comment_status' => 'open',
            'supports' => ['title', 'editor', 'custom-fields', 'comments','thumbnail'],
        ]);

        // Bác sĩ
        register_post_type('doctor', [
            'label' => __('Bác sĩ', 'nex2tek-qa'),
            'public' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-groups',
            'supports' => ['title', 'editor', 'custom-fields','thumbnail'],
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
        
        $paged = max(1, get_query_var('paged') ?: get_query_var('page') ?: 1);

        $query = new WP_Query([
            'post_type'      => 'question',
            'post_status'    => 'publish',
            'posts_per_page' => 12,
            'paged'          => $paged,
        ]);
   
        $template_file = plugin_dir_path(__FILE__) . 'templates/shortcode-qa-list.php';
        if (file_exists($template_file)) {
            include $template_file;
        }
        return ob_get_clean();
    }


    public function qa_form_shortcode() {
        ob_start();

        $success = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['qa_question'])) {
            $question_content = sanitize_textarea_field($_POST['qa_question']);
            $name  = sanitize_text_field($_POST['qa_name']);
            $phone = sanitize_text_field($_POST['qa_phone']);
            $email = sanitize_email($_POST['qa_email']);

            $meta_data = [
                'qa_name'  => $name,
                'qa_phone' => $phone,
                'qa_email' => $email,
            ];

            $post_id = wp_insert_post([
                'post_type'    => 'question',
                'post_title'   => wp_trim_words($question_content, 10),
                'post_content' => $question_content,
                'post_status'  => 'pending',
                'meta_input'   => $meta_data,
            ]);

            if ($post_id) {
                $success = true;
            }
        }

        $template_file = plugin_dir_path(__FILE__) . 'templates/shortcode-qa-form.php';
        if(file_exists($template_file)) {
            include $template_file;
        }

        return ob_get_clean();
    }    
    
    public function qa_question_category_shortcode() {
        // Get the list of terms belonging to the 'question_category' taxonomy
        $terms = get_terms([
            'taxonomy' => 'question_category',
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC',
        ]);
    
        if (is_wp_error($terms) || empty($terms)) {
            return ''; // Return an empty string if no terms are found
        }
    
        ob_start();
        ?>
    
        <div class="qa-category-list p-3">
            <h5>Chuyên mục</h5>
            <ul class="list-unstyled mb-0">
                <?php foreach ($terms as $term): ?>
                    <li class="mb-2">
                        <a href="<?php echo esc_url(get_term_link($term)); ?>" class="text-dark text-decoration-none">
                            <?php echo esc_html($term->name); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    
        <?php
        return ob_get_clean();
    }
    
    public function qa_question_statistic_shortcode() {
        ob_start();
    
        // --- Top viewed questions ---
        $top_viewed_questions = new WP_Query([
            'post_type'      => 'question',
            'posts_per_page' => 5,
            'post_status'    => 'publish',
            'meta_key'       => 'view_count',
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
        ]);
    
        if ($top_viewed_questions->have_posts()):
            ?>
            <div class="qa-most-viewed">
                <h5>Câu hỏi được xem nhiều nhất</h5>
                <ol class="qa-most-viewed-list list-unstyled mb-0">
                    <?php $i = 1; while ($top_viewed_questions->have_posts()): $top_viewed_questions->the_post(); ?>
                        <li>
                            <div class="qa-most-viewed-item">
                                <?php echo $i++ . '. '; ?>
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </div>
                            <div>
                                <small class="qa-view-count">
                                    (<?php echo number_format((int) get_post_meta(get_the_ID(), 'view_count', true)); ?> lượt xem)
                                </small>
                            </div>
                        </li>
                    <?php endwhile; wp_reset_postdata(); ?>
                </ol>
            </div>
            <?php
        endif;
    
        // --- Top commented questions ---
        global $wpdb;

        $post_ids = $wpdb->get_col("
            SELECT ID FROM {$wpdb->posts}
            WHERE post_type = 'question'
            AND post_status = 'publish'
            AND comment_count > 0
            ORDER BY comment_count DESC
            LIMIT 5
        ");

        if (empty($post_ids)) return '';

        $most_commented_questions = new WP_Query([
            'post_type' => 'question',
            'post__in' => $post_ids,
            'orderby' => 'post__in',
            'posts_per_page' => 5
        ]);

        if ($most_commented_questions->have_posts()):
            ?>
            <div class="qa-most-viewed">
                <h5>Câu hỏi nhiều bình luận nhất</h5>
                <ol class="qa-most-viewed-list list-unstyled mb-0">
                    <?php $i = 1; while ($most_commented_questions->have_posts()): $most_commented_questions->the_post(); ?>
                        <li>
                            <div class="qa-most-viewed-item">
                                <?php echo $i++ . '. '; ?>
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </div>
                            <div>
                                <small class="qa-view-count">
                                    (<?php echo get_comments_number(); ?> comment)
                                </small>
                            </div>
                        </li>
                    <?php endwhile; wp_reset_postdata(); ?>
                </ol>
            </div>
            <?php
        endif;
    
        return ob_get_clean();
    }        

    public function override_templates($template) {
        // Giao diện chi tiết câu hỏi
        if (is_singular('question')) {
            $custom_template = plugin_dir_path(__FILE__) . 'templates/single-question.php';
            if (file_exists($custom_template)) return $custom_template;
        }
        // Giao diện chi tiết bác sĩ
        if (is_singular('doctor')) {
            $custom_template = plugin_dir_path(__FILE__) . 'templates/single-doctor.php';
            if (file_exists($custom_template)) return $custom_template;
        }

        if(is_page('gui-cau-hoi') || is_page('hoi-dap')) {
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
        wp_enqueue_style('nex2tek-qa-style', plugin_dir_url(__FILE__) . 'assets/style.css', [], '1.0.0');

        // Scripts
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

function increase_question_view_count($post_id) {
    if (get_post_type($post_id) !== 'question') return;

    $views = (int) get_post_meta($post_id, 'view_count', true);
    $views++;
    update_post_meta($post_id, 'view_count', $views);
}

