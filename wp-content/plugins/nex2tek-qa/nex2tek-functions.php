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
    register_setting('nex2tek_qa_settings_group', 'nex2tek_qa_enable_captcha');

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

    // Enable captcha field
    add_settings_field(
        'nex2tek_qa_enable_captcha',
        __('Enable CAPTCHA', 'nex2tek-qa'),
        'nex2tek_qa_enable_captcha_field_render',
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

function nex2tek_qa_enable_captcha_field_render() {
    $enabled = get_option('nex2tek_qa_enable_captcha', false);
    ?>
    <label>
        <input type="checkbox" name="nex2tek_qa_enable_captcha" value="1" <?php checked(1, $enabled); ?> />
        <?php _e('Enable Cloudflare Turnstile CAPTCHA', 'nex2tek-qa'); ?>
    </label>
    <?php
}

function nex2tek_breadcrumb() {
    echo '<div class="qa-breadcrumb">';
    echo '<a href="' . home_url() . '">' . nex2tek_text('Trang chủ', 'nex2tek-qa') .'</a> &nbsp;&gt;&nbsp; ';

    $is_page_question      = get_translated_page_id_by_slug('hoi-dap');
    $page_qa_id = get_page_by_path('hoi-dap');
    $page_qa_id = $page_qa_id ? $page_qa_id->ID : 0;
    $link = function_exists('pll_get_post') ? get_permalink(pll_get_post($page_qa_id)) : get_permalink($page_qa_id);
    
    if ( (is_category() || is_tax() ) && !is_page($is_page_question) ) {
        echo '<a href="' . $link . '">' . nex2tek_text('Hỏi đáp', 'nex2tek-qa') .'</a> &nbsp;&gt;&nbsp; ';
        echo '<span>' . single_tag_title('', false) . '</span>';
    } else {
        echo '<a href="' . $link . '">' . nex2tek_text('Hỏi đáp', 'nex2tek-qa') .'</a> &nbsp;&gt;&nbsp; ';
        echo '<span>' . get_the_title() . '</span>';
    }

    echo '</div>';
}

function get_translated_page_id_by_slug($slug) {
    $page = get_page_by_path($slug);
    if (!$page) return false;
    return function_exists('pll_get_post') ? pll_get_post($page->ID) : $page->ID;
}
