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
        
        $paged = get_query_var('paged') ?: 1;

        $query = new WP_Query([
            'post_type'      => 'question',
            'post_status'    => 'publish',
            'posts_per_page' => 10,
            'paged'          => $paged,
        ]);
    ?>
    <div class="container mt-4">
        <div class="row">
            <!-- Sidebar chuyên mục -->
            <div class="col-lg-2">
                <?php echo do_shortcode('[nex2tek_qa_question_category]'); ?>
            </div>

            <!-- Danh sách câu hỏi -->
            <div class="col-lg-7">
                <?php if ($query->have_posts()) : ?>
                    <ul class="qa-list list-unstyled">
                        <?php while ($query->have_posts()) : $query->the_post();?>
                            <li class="mb-4 border-bottom pb-3">
                                <h5 class="fw-bold mb-2"><?php the_title(); ?></h5>
                                <div class="text-muted mb-2">
                                    <small class="text-primary">
                                        (<?php echo number_format((int) get_post_meta(get_the_ID(), 'view_count', true)); ?> lượt xem)
                                    </small>
                                </div>
                                
                            </li>
                        <?php endwhile; ?>
                    </ul>

                    <!-- Pagination -->
                    <div class="qa-pagination mt-4">
                        <?php
                        echo paginate_links([
                            'total'   => $query->max_num_pages,
                            'current' => $paged,
                            'prev_text' => '&laquo;',
                            'next_text' => '&raquo;',
                        ]);
                        ?>
                    </div>
                <?php else : ?>
                    <p class="text-muted"><?php _e('Không có câu hỏi nào.', 'nex2tek-qa'); ?></p>
                <?php endif; ?>
                <?php wp_reset_postdata(); ?>
            </div>

            <!-- Thống kê bên phải -->
            <div class="col-lg-3">
                <?php echo do_shortcode('[nex2tek_qa_question_statistic]'); ?>
            </div>
        </div>
    </div>
    <?php
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
                echo '<div class="alert alert-success">Câu hỏi của bạn đã được gửi thành công.</div>';
            }
        }
        ?>
        <div class="container mt-4">
            <div class="row">
                <div class="col-lg-2">
                    <?php echo do_shortcode('[nex2tek_qa_question_category]'); ?>
                </div>
                <div class="col-lg-7">
                    <div class="qa-form-wrapper p-4 rounded-4 shadow-sm bg-white">
                        <h3 class="fw-bold mb-2">ĐẶT CÂU HỎI</h3>
                        <p class="text-muted mb-4">Quý khách vui lòng điền đầy đủ thông bên dưới</p>
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
                <div class="col-lg-3">
                    <?php echo do_shortcode('[nex2tek_qa_question_statistic]'); ?>
                </div>
            </div>
        </div>

        <?php
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
            <h5 class="fw-bold text-primary mb-2">Chuyên mục</h5>
            <hr class="my-2" />
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
        // Get top 5 'question' posts with the highest view count (based on meta_key 'view_count')
        $top_questions = new WP_Query([
            'post_type'      => 'question',
            'posts_per_page' => 5,
            'post_status'    => 'publish',
            'meta_key'       => 'view_count',
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
        ]);
    
        if (!$top_questions->have_posts()) {
            return '';
        }
    
        ob_start();
        ?>
    
        <div class="qa-most-viewed p-3 bg-white rounded-4 shadow-sm">
            <h5 class="fw-bold text-primary mb-3">Câu hỏi được xem nhiều nhất</h5>
            <ol class="list-unstyled mb-0">
                <?php $i = 1; while ($top_questions->have_posts()): $top_questions->the_post(); ?>
                    <li class="mb-3">
                        <div class="fw-semibold"><?php echo $i++ . '. '; ?><a href="<?php the_permalink(); ?>" class="text-dark text-decoration-none"><?php the_title(); ?></a></div>
                        <div>
                            <small class="text-primary">
                                (<?php echo number_format((int) get_post_meta(get_the_ID(), 'view_count', true)); ?> lượt xem)
                            </small>
                        </div>
                        <?php if ($i <= 6) echo '<hr class="my-2">'; ?>
                    </li>
                <?php endwhile; wp_reset_postdata(); ?>
            </ol>
        </div>
    
        <?php
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

