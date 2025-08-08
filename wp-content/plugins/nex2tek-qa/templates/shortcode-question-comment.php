<?php

    global $wpdb;
    $current_lang = get_current_lang();

    $post_ids = $wpdb->get_col("
        SELECT ID FROM {$wpdb->posts}
        WHERE post_type = 'question'
        AND post_status = 'publish'
        AND comment_count > 0
        ORDER BY comment_count DESC
        LIMIT 5
    ");

    if (empty($post_ids)) return '';

    $args = [
        'post_type' => 'question',
        'post__in' => $post_ids,
        'orderby' => 'post__in',
        'posts_per_page' => 5,
    ];

    // Only add tax_query if the 'language' taxonomy exists and current_lang is set
    if (taxonomy_exists('language') && !empty($current_lang)) {
        $args['tax_query'] = [
            [
                'taxonomy' => 'language',
                'field'    => 'slug',
                'terms'    => $current_lang,
            ],
        ];
    }

    $most_commented_questions = new WP_Query($args);

    if ($most_commented_questions->have_posts()):
?>
<div class="qa-most-viewed">
    <div class="qa-most-viewed-title"><?php nex2tek_echo('Câu hỏi nhiều bình luận nhất', 'nex2tek-qa'); ?></div>
    <ol class="qa-most-viewed-list">
        <?php $i = 1; while ($most_commented_questions->have_posts()): $most_commented_questions->the_post(); ?>
            <li>
                <div class="qa-most-viewed-item">
                    <?php echo $i++ . '. '; ?>
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </div>
                <div class="question-icon-wrapper">
                    <span class="question-icon"><i class="bi bi-chat-dots"></i></span>
                    <a href="<?php the_permalink(); ?>" class="text-decoration-none">
                        <span><?php echo get_comments_number(); ?> <?php nex2tek_echo('bình luận','nex2tek-qa'); ?></span>
                    </a>
                </div>
            </li>
        <?php endwhile; wp_reset_postdata(); ?>
    </ol>
</div>
<?php endif; ?>
