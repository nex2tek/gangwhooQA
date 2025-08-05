<?php get_header(); 
 increase_question_view_count(get_the_ID());
?>

<main class="qa-single">
    <h1><?php the_title(); ?></h1>
    <div class="question-content"><?php the_content(); ?></div>

    <?php
    $answer = get_post_meta(get_the_ID(), '_answer', true);
    if ($answer): ?>
        <div class="answer-box">
            <h2><?php _e('Trả lời:', 'nex2tek-qa'); ?></h2>
            <div><?php echo wpautop($answer); ?></div>
        </div>
    <?php endif; ?>
    <?php
        if (comments_open() || get_comments_number()) {
            comments_template();
        }
    ?>
</main>

<?php get_footer(); ?>
