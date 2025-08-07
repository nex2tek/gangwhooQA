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
?>
<div class="qa-category-list p-3">
    <h5><?php _e('Chuyên Mục', 'nex2tek-qa'); ?></h5>
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