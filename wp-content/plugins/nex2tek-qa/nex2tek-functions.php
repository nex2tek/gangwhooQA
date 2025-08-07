<?php
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

// apply polylang support
add_filter('pll_get_post_types', function ($post_types, $is_translatable) {
    $post_types['question'] = 'question';
    $post_types['doctor'] = 'doctor';

    return $post_types;
}, 10, 2);

// apply polylang support
add_filter('pll_get_taxonomies', function ($taxonomies, $is_translatable) {
    if ($is_translatable) {
        $taxonomies['question_category'] = 'question_category';
    }
    return $taxonomies;
}, 10, 2);

function increase_question_view_count($post_id) {
    if (get_post_type($post_id) !== 'question') return;

    $views = (int) get_post_meta($post_id, 'view_count', true);
    $views++;
    update_post_meta($post_id, 'view_count', $views);
}

// Register admin menu
add_action('admin_menu', 'nex2tek_qa_register_settings_menu');
function nex2tek_qa_register_settings_menu() {
    add_options_page(
        'Nex2Tek QA Settings',      // Page title
        'Nex2Tek QA',               // Menu title
        'manage_options',           // Capability
        'nex2tek-qa-settings',      // Slug
        'nex2tek_qa_settings_page'  // Callback to render
    );
}

function nex2tek_qa_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('Nex2Tek QA Settings', 'nex2tek-qa'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('nex2tek_qa_settings_group');   // Security fields
            do_settings_sections('nex2tek-qa-settings');    // Settings sections & fields
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

add_action('admin_init', 'nex2tek_qa_register_settings');
function nex2tek_qa_register_settings() {
    // Register settings
    register_setting('nex2tek_qa_settings_group', 'nex2tek_qa_sitekey');
    register_setting('nex2tek_qa_settings_group', 'nex2tek_qa_secretkey');

    // Add settings section
    add_settings_section(
        'nex2tek_qa_section',
        __('Cloudflare Turnstile', 'nex2tek-qa'),
        null,
        'nex2tek-qa-settings'
    );

    // Sitekey field
    add_settings_field(
        'nex2tek_qa_sitekey',
        __('Site Key', 'nex2tek-qa'),
        'nex2tek_qa_sitekey_field_render',
        'nex2tek-qa-settings',
        'nex2tek_qa_section'
    );

    // Secret key field
    add_settings_field(
        'nex2tek_qa_secretkey',
        __('Secret Key', 'nex2tek-qa'),
        'nex2tek_qa_secretkey_field_render',
        'nex2tek-qa-settings',
        'nex2tek_qa_section'
    );
}

function nex2tek_qa_sitekey_field_render() {
    $value = get_option('nex2tek_qa_sitekey', '');
    echo '<input type="text" name="nex2tek_qa_sitekey" value="' . esc_attr($value) . '" class="regular-text" />';
}

function nex2tek_qa_secretkey_field_render() {
    $value = get_option('nex2tek_qa_secretkey', '');
    echo '<input type="text" name="nex2tek_qa_secretkey" value="' . esc_attr($value) . '" class="regular-text" />';
}

function nex2tek_breadcrumb() {
    echo '<div class="qa-breadcrumb">';
    echo '<a href="' . home_url() . '">Trang chủ</a> &nbsp;&gt;&nbsp; ';

    if (is_singular('doctor')) {
        echo '<a href="' . site_url('/hoi-dap') . '">Hỏi đáp</a> &nbsp;&gt;&nbsp; ';
        echo '<span>' . get_the_title() . '</span>';

    } elseif (is_singular('question')) {
        echo '<a href="' . site_url('/hoi-dap') . '">Hỏi đáp</a> &nbsp;&gt;&nbsp; ';

        $terms = get_the_terms(get_the_ID(), 'question_category');
        if (!empty($terms) && !is_wp_error($terms)) {
            $term = $terms[0]; // lấy category đầu tiên
            echo '<a href="' . get_term_link($term) . '">' . esc_html($term->name) . '</a> &nbsp;&gt;&nbsp; ';
        }

        echo '<span>' . get_the_title() . '</span>';

    } elseif (is_post_type_archive('doctor')) {
        echo '<a href="' . site_url('/hoi-dap') . '">Hỏi đáp</a> &nbsp;&gt;&nbsp; ';
        echo '<span>Bác sĩ</span>';

    } elseif (is_post_type_archive('question')) {
        echo '<span>Hỏi đáp</span>';

    } elseif (is_tax('question_category')) {
        echo '<a href="' . site_url('/hoi-dap') . '">Hỏi đáp</a> &nbsp;&gt;&nbsp; ';
        echo '<span>' . single_term_title('', false) . '</span>';

    } else {
        echo '<span>' . get_the_title() . '</span>';
    }

    echo '</div>';
}
