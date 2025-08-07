<?php
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
    <h5><?php _e('Câu hỏi được xem nhiều nhất', 'nex2tek-qa'); ?></h5>
    <ol class="qa-most-viewed-list list-unstyled mb-0">
        <?php $i = 1; while ($top_viewed_questions->have_posts()): $top_viewed_questions->the_post(); ?>
            <li>
                <div class="qa-most-viewed-item">
                    <?php echo $i++ . '. '; ?>
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </div>
                <div>
                    <small class="qa-view-count">
                        (<?php echo number_format((int) get_post_meta(get_the_ID(), 'view_count', true)); ?> <?php _e('lượt xem','nex2tek-qa'); ?>)
                    </small>
                </div>
            </li>
        <?php endwhile; wp_reset_postdata(); ?>
    </ol>
</div>

<?php endif; ?>