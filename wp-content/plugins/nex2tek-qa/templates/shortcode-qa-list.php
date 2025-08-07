<?php

if (!defined('ABSPATH')) exit;

$current_lang = get_current_lang();

$paged = max(1, get_query_var('paged') ?: get_query_var('page') ?: 1);

$query = new WP_Query([
    'post_type'      => 'question',
    'post_status'    => 'publish',
    'posts_per_page' => 12,
    'paged'          => $paged,
    'tax_query'      => [
        [
            'taxonomy' => 'language',
            'field'    => 'slug',
            'terms'    => $current_lang,
        ],
    ],
]);
?>

<div class="qa-container">
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
                <div class="qa-list list-unstyled">
                    <?php while ($query->have_posts()) : $query->the_post();?>
                        <div class="mb-4 border-bottom pb-3 qa-item">
                            <h3 class="fw-bold mb-2"> <a href="<?php the_permalink(); ?>" class="text-dark text-decoration-none"><?php the_title(); ?></a></h3>
                            <div class="text-muted mb-2">
                                <div class="text-primary d-flex align-items-center">    
                                    <img class="qa-avatar" src="<?php echo plugins_url('assets/images/avatar.png', __DIR__); ?>" width="30" height="30"> <span class="qa-name"> <?php echo get_post_meta(get_the_ID(), 'qa_name', true) ?: nex2tek_echo('Người ẩn danh', 'nex2tek-qa'); ?> </span>
                                </div>
                            </div>
                            <div class="description mb-2"><?php the_excerpt(); ?></div>
                            <div class="text-muted mb-2">
                                <small class="text-primary">
                                    (<?php echo number_format((int) get_post_meta(get_the_ID(), 'view_count', true)); ?> <?php nex2tek_echo('lượt xem', 'nex2tek-qa'); ?>)
                                </small>
                            </div>
                            
                        </div>
                    <?php endwhile; ?>
                </div>

              <!-- Pagination -->
              <div class="qa-pagination mt-4">
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
              <p class="text-muted"><?php nex2tek_echo('Không có câu hỏi nào.', 'nex2tek-qa'); ?></p>
          <?php endif; ?>
          <?php wp_reset_postdata(); ?>
            </div>
        </div>

        <!-- Sidebar right -->
        <div class="qa-col qa-sidebar-right">
            <?php echo do_shortcode('[nex2tek_qa_question_view]'); ?>
            <?php echo do_shortcode('[nex2tek_qa_question_comment]'); ?>
        </div>
    </div>
</div>
