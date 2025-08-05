<?php get_header(); ?>

<main class="qa-archive">
    <h1><?php _e('Page cau hoi', 'nex2tek-qa'); ?></h1>

    <?php if (have_posts()) : ?>
        <ul class="qa-list">
            <?php while (have_posts()) : the_post(); ?>
                <?php the_content(); ?>
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
