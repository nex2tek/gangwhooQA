<?php get_header(); ?>

<main class="single-doctor">
    <h1><?php the_title(); ?></h1>
    <div class="doctor-content"><?php the_content(); ?></div>

    <hr>

    <h2><?php _e('Các câu hỏi đã được giao:', 'nex2tek-qa'); ?></h2>

    <ul class="doctor-questions">
        <?php
        $doctor_id = get_the_ID();
        $questions = new WP_Query([
            'post_type'  => 'question',
            'meta_key'   => '_select_doctor',
            'meta_value' => $doctor_id,
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ]);

        if ($questions->have_posts()) :
            while ($questions->have_posts()) : $questions->the_post();
                echo '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
            endwhile;
            wp_reset_postdata();
        else :
            echo '<li>' . __('Chưa có câu hỏi nào.', 'nex2tek-qa') . '</li>';
        endif;
        ?>
    </ul>
</main>

<?php get_footer(); ?>
