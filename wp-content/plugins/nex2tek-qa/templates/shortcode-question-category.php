<?php
if (!defined('ABSPATH')) exit; 
$current_lang = get_current_lang();

$terms = get_terms([
    'taxonomy' => 'question_category',
    'hide_empty' => false,
    'orderby' => 'name',
    'order' => 'ASC',
    'lang'  => $current_lang
]);

if (is_wp_error($terms) || empty($terms)) {
    return ''; // Return an empty string if no terms are found
}

// Get current category slug (if any)
$current_term = get_queried_object();
$current_slug = ($current_term && isset($current_term->slug)) ? $current_term->slug : '';

?>
<div class="qa-category-list">
    <div class="qa-category-title"><?php nex2tek_echo('Chuyên mục', 'nex2tek-qa'); ?></div>
    <ul>
        <?php foreach ($terms as $term): ?>
            <?php
                $is_active = $term->slug === $current_slug ? 'qa-active' : '';
            ?>
            <li class="mb-2 <?php echo esc_attr($is_active); ?>">
                <a href="<?php echo esc_url(get_term_link($term)); ?>">
                    <?php echo esc_html($term->name); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>