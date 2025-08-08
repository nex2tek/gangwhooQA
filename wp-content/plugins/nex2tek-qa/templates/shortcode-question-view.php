<?php
    $current_lang = get_current_lang();

    // Base args
    $args = [
        'post_type'      => 'question',
        'posts_per_page' => 5,
        'post_status'    => 'publish',
        'meta_key'       => 'view_count',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
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

    $top_viewed_questions = new WP_Query($args);

    if ($top_viewed_questions->have_posts()):
?>

<div class="qa-most-viewed">
    <h5><?php nex2tek_echo('Câu hỏi được xem nhiều nhất', 'nex2tek-qa'); ?></h5>
    <ol class="qa-most-viewed-list">
        <?php $i = 1; while ($top_viewed_questions->have_posts()): $top_viewed_questions->the_post(); ?>
            <li>
                <div class="qa-most-viewed-item">
                    <?php echo $i++ . '. '; ?>
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </div>
                <div class="question-icon-wrapper">
                    <span class="question-icon eye-icon"></span>
                    <span><?php echo number_format((int) get_post_meta(get_the_ID(), 'view_count', true)); ?> <?php nex2tek_echo('lượt xem','nex2tek-qa'); ?></span>
                </div>
            </li>
        <?php endwhile; wp_reset_postdata(); ?>
    </ol>
</div>

<?php endif; ?>
