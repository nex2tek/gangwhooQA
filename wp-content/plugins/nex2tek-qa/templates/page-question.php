<?php get_header(); ?>

<main class="qa-archive">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <?php the_content(); ?>
        <?php endwhile; ?>
    <?php else : ?>
        <p><?php nex2tek_echo('Chưa có câu hỏi nào.', 'nex2tek-qa'); ?></p>
    <?php endif; ?>
</main>

<?php get_footer(); ?>
