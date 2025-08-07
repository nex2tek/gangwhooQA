<?php get_header(); ?>

<div class="qa-container container mt-4">
    <div class="qa-doctor-details-profile">
        <?php nex2tek_breadcrumb(); ?>

        <div class="qa-doctor-details-card">
            <div class="qa-doctor-details-image">
                <?php the_post_thumbnail('medium'); ?>
            </div>
            <div class="qa-doctor-details-info">
                <h3 class="qa-doctor-title"><?php echo esc_html(get_post_meta(get_the_ID(), 'doctor_title', true)); ?></h3>
                <h2><?php the_title(); ?></h2>

                <div class="qa-doctor-details-desc">
                    <?php the_content(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>