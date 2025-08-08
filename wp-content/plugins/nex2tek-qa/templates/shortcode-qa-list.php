<?php

if (!defined('ABSPATH')) exit;

$current_lang = get_current_lang();

$paged = max(1, get_query_var('paged') ?: get_query_var('page') ?: 1);

$args = [
    'post_type'      => 'question',
    'post_status'    => 'publish',
    'posts_per_page' => 12,
    'paged'          => $paged,
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

$query = new WP_Query($args);
?>

<div class="qa-container">
    <?php nex2tek_breadcrumb(); ?>
    <div class="qa-row">
        <!-- Sidebar Left -->
        <div class="qa-col qa-sidebar-left">
            <?php echo do_shortcode('[nex2tek_qa_question_category]'); ?>
        </div>

        <!-- Form Question -->
        <div class="qa-col qa-main-form">
            <?php echo do_shortcode('[nex2tek_qa_doctor_list]'); ?>
            <div class="qa-form-wrapper">
               <?php if ($query->have_posts()) : ?>
                <div class="qa-list">
                    <?php while ($query->have_posts()) : $query->the_post();?>
                        <div class="border-bottom qa-item">
                            <h3> <a href="<?php the_permalink(); ?>" class="text-dark text-decoration-none"><?php the_title(); ?></a></h3>
                            <div>
                                <div class="text-primary d-flex align-items-center">    
                                    <img class="qa-avatar" src="<?php echo plugins_url('assets/images/avatar.png', __DIR__); ?>" width="30" height="30"> <span class="qa-name"> <?php echo get_post_meta(get_the_ID(), 'qa_name', true) ?: nex2tek_echo('Người ẩn danh', 'nex2tek-qa'); ?> </span>
                                </div>
                            </div>
                            <div class="description"><?php the_excerpt(); ?></div>
                            <div class="question-icon-wrapper">
                                <span class="question-icon comment-icon"></span>
                                <span><?php echo number_format((int) get_post_meta(get_the_ID(), 'view_count', true)); ?> <?php nex2tek_echo('lượt xem', 'nex2tek-qa'); ?></span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

              <!-- Pagination -->
              <div class="qa-pagination">
                  <?php
                  echo paginate_links([
                      'total'   => $query->max_num_pages,
                      'current' => $paged,
                      'prev_text' => '&laquo;',
                      'next_text' => '&raquo;',
                  ]);
                  ?>
              </div>
          <?php else : ?>
              <p><?php nex2tek_echo('Không có câu hỏi nào', 'nex2tek-qa'); ?>.</p>
          <?php endif; ?>
          <?php wp_reset_postdata(); ?>
            </div>
        </div>

        <!-- Sidebar right -->
        <div class="qa-col qa-sidebar-right">
            <?php echo do_shortcode('[nex2tek_qa_button_create_question]'); ?>
            <?php echo do_shortcode('[nex2tek_qa_question_view]'); ?>
            <?php echo do_shortcode('[nex2tek_qa_question_comment]'); ?>
            
        </div>
    </div>
</div>
