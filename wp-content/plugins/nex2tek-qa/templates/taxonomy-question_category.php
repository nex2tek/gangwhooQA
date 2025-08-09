<?php get_header(); 

$paged = max(1, get_query_var('paged'));

$term = get_queried_object();

$query = new WP_Query([
    'post_type'      => 'question',
    'tax_query'      => [
        [
            'taxonomy' => 'question_category',
            'field'    => 'slug',
            'terms'    => $term->slug,
        ],
    ],
    'post_status'    => 'publish',
    'posts_per_page' => 12,
    'paged'          => $paged,
]);
?>

<div class="qa-container">
    <?php nex2tek_breadcrumb(); ?>
    <div class="qa-row">
        <!-- Sidebar chuyên mục -->
        <div class="qa-col qa-sidebar-left">
            <?php echo do_shortcode('[nex2tek_qa_question_category]'); ?>
        </div>

        <!-- Form câu hỏi -->
        <div class="qa-col qa-main-form">
            <div class="qa-form-wrapper qa-lists">
               <h1 class="text-center"><?php single_term_title(); ?></h1>
               <div class="qa-cat-description">
                   <?php echo $term->description; ?>
                    <p class="text-center">
                       <?php nex2tek_echo('Có', 'nex2tek-qa'); ?> <span><?php echo $query->found_posts; ?></span>  <?php nex2tek_echo('câu hỏi cho chủ đề này', 'nex2tek-qa'); ?>
                    </p>
               </div>
               <?php if ($query->have_posts()) : ?>
                <div class="qa-list">
                    <?php while ($query->have_posts()) : $query->the_post();?>
                        <div class="qa-item">
                            <h3> <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            
                            <div>
                                <div class="text-primary d-flex align-items-center">    
                                    <img class="qa-avatar" src="<?php echo plugins_url('assets/images/avatar.png', __DIR__); ?>" width="30" height="30"><?php echo get_post_meta(get_the_ID(), 'qa_name', true) ?: nex2tek_echo('Người ẩn danh', 'nex2tek-qa') ; ?>
                                </div>
                            </div>
                            <div class="description"><?php the_excerpt(); ?></div>
                            <div class="question-icon-wrapper">
                                <span class="question-icon"><i class="bi bi-eye"></i></span>
                                <span><?php echo number_format((int) get_post_meta(get_the_ID(), 'view_count', true)); ?> <?php nex2tek_echo('lượt xem', 'nex2tek-qa'); ?></span>
                            </div>
                             <div class="question-icon-wrapper">
                                <span class="question-icon"><i class="bi bi-chat-dots"></i></span>
                                <span><span><?= get_comments_number() ?> <?php nex2tek_echo('bình luận','nex2tek-qa'); ?></span></span>
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

        <!-- Sidebar thống kê -->
        <div class="qa-col qa-sidebar-right">
            <?php echo do_shortcode('[nex2tek_qa_button_create_question]'); ?>
            <?php echo do_shortcode('[nex2tek_qa_question_view]'); ?>
            <?php echo do_shortcode('[nex2tek_qa_question_comment]'); ?>
        </div>
    </div>
</div>
<?php get_footer(); ?>
