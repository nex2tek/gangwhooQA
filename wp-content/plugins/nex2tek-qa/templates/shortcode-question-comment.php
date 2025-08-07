<?php

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
    <h5><?php _e('Câu hỏi nhiều bình luận nhất', 'nex2tek-qa'); ?></h5>
    <ol class="qa-most-viewed-list list-unstyled mb-0">
        <?php $i = 1; while ($most_commented_questions->have_posts()): $most_commented_questions->the_post(); ?>
            <li>
                <div class="qa-most-viewed-item">
                    <?php echo $i++ . '. '; ?>
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </div>
                <div>
                    <small class="qa-view-count">
                        (<?php echo get_comments_number(); ?> <?php _e('bình luận','nex2tek-qa'); ?>)
                    </small>
                </div>
            </li>
        <?php endwhile; wp_reset_postdata(); ?>
    </ol>
</div>
<?php endif; ?>