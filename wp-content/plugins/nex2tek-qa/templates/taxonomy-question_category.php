<?php get_header(); ?>

<main class="qa-taxonomy-category">
    <h1><?php single_term_title(); ?></h1>
    <p><?php echo term_description(); ?></p>

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
    <?php else : ?>
        <p><?php _e('Không có câu hỏi nào trong chuyên mục này.', 'nex2tek-qa'); ?></p>
    <?php endif; ?>
</main>

<?php get_footer(); ?>
