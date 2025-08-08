<?php

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
    register_setting('nex2tek_qa_settings_group', 'nex2tek_qa_enable_breadcrumb');

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

    // Enable breadcrumb field
    add_settings_field(
        'nex2tek_qa_enable_breadcrumb',
        __('Enable Breadcrumb', 'nex2tek-qa'),
        'nex2tek_qa_enable_breadcrumb_field_render',
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

function nex2tek_qa_enable_breadcrumb_field_render() {
    $value = get_option('nex2tek_qa_enable_breadcrumb', 1);
    echo '<input type="checkbox" name="nex2tek_qa_enable_breadcrumb" value="1" ' . checked(1, $value, false) . ' />';
}

function nex2tek_breadcrumb() {
    if (!get_option('nex2tek_qa_enable_breadcrumb', 1)) {
        return;
    }
    // Home link
    $home_url   = esc_url(home_url('/'));
    $home_label = esc_html(nex2tek_text('Trang chủ', 'nex2tek-qa'));
    $qa = new Nex2Tek_QA();
    // Q&A link page
    $qa_page_id = $qa->get_translated_page_id_by_slug('hoi-dap');
  
    $qa_url     = $qa_page_id ? get_permalink((int) $qa_page_id) : '#';
    $qa_label   = esc_html(nex2tek_text('Hỏi đáp', 'nex2tek-qa'));

    echo '<div class="qa-breadcrumb">';
    echo '<a href="' . $home_url . '">' . $home_label . '</a> &nbsp;&gt;&nbsp; ';

    // Q&A link
    if (!is_page($qa_page_id)) {
        echo '<a href="' . esc_url($qa_url) . '">' . $qa_label . '</a> &nbsp;&gt;&nbsp; ';
    }

    // Last breadcrumb
    if ((is_category() || is_tax()) && !is_page($qa_page_id)) {
        echo '<span>' . esc_html(single_term_title('', false)) . '</span>';
    } else {
        echo '<span>' . esc_html(get_the_title()) . '</span>';
    }

    echo '</div>';
}