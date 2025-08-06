<?php
/** @var WP_Query $query */
/** @var int $paged */
?>
<div class="container mt-4">
  <div class="row">
      <!-- Sidebar chuyên mục -->
      <div class="col-lg-2">
          <?php echo do_shortcode('[nex2tek_qa_question_category]'); ?>
      </div>

      <!-- Danh sách câu hỏi -->
      <div class="col-lg-7">
          <?php if ($query->have_posts()) : ?>
              <ul class="qa-list list-unstyled">
                  <?php while ($query->have_posts()) : $query->the_post();?>
                      <li class="mb-4 border-bottom pb-3">
                          <h5 class="fw-bold mb-2"> <a href="<?php the_permalink(); ?>" class="text-dark text-decoration-none"><?php the_title(); ?></a></h5>
                          <div class="text-muted mb-2">
                              <small class="text-primary">
                                  <i class="bi bi-person"></i> <?php echo get_post_meta(get_the_ID(), 'qa_name', true); ?>
                              </small>
                          </div>
                          <div class="description mb-2"><?php the_excerpt(); ?></div>
                          <div class="text-muted mb-2">
                              <small class="text-primary">
                                  (<?php echo number_format((int) get_post_meta(get_the_ID(), 'view_count', true)); ?> lượt xem)
                              </small>
                          </div>
                          
                      </li>
                  <?php endwhile; ?>
              </ul>

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
              <p class="text-muted"><?php _e('Không có câu hỏi nào.', 'nex2tek-qa'); ?></p>
          <?php endif; ?>
          <?php wp_reset_postdata(); ?>
      </div>

      <!-- Thống kê bên phải -->
      <div class="col-lg-3">
          <?php echo do_shortcode('[nex2tek_qa_question_statistic]'); ?>
      </div>
  </div>
</div>