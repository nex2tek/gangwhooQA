<?php get_header(); ?>

<main class="qa-archive">
    <h1><?php _e('Danh sách câu hỏi', 'nex2tek-qa'); ?></h1>

    <?php if (have_posts()) : ?>
        <ul class="qa-list">
            <?php while (have_posts()) : the_post(); ?>
                <li>
                    <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                    <p><?php the_excerpt(); ?></p>
                    <?php
                    $answer = get_post_meta(get_the_ID(), '_answer', true);
                    if ($answer) {
                        echo '<div class="qa-answer"><strong>' . __('Trả lời:', 'nex2tek-qa') . '</strong><br>' . wpautop($answer) . '</div>';
                    }
                    ?>
                </li>
            <?php endwhile; ?>
        </ul>

        <div class="qa-pagination">
            <?php the_posts_pagination(); ?>
        </div>

    <?php else : ?>
        <p><?php _e('Chưa có câu hỏi nào.', 'nex2tek-qa'); ?></p>
    <?php endif; ?>
</main>

<?php get_footer(); ?>
