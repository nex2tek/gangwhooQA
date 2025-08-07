<?php
// Get all published doctors
$doctors = new WP_Query([
    'post_type'      => 'doctor',
    'posts_per_page' => 10,
    'post_status'    => 'publish',
]);

if (!$doctors->have_posts()) {
    return '';
}
?>

<div class="qa-doctor-grid-wrapper">
  <div class="qa-doctor-grid">
      <?php
      $i = 0;
      while ($doctors->have_posts()): $doctors->the_post();
          $hidden = $i >= 4 ? ' qa-doctor-hidden' : '';
          ?>
          <div class="qa-doctor-card<?php echo $hidden; ?>">
              <a href="<?php the_permalink(); ?>">
                  <div class="qa-doctor-avatar">
                          <?php if (has_post_thumbnail()) {
                              the_post_thumbnail('medium');
                          } ?>
                  </div>
                  <p class="qa-doctor-title"><?php echo esc_html(get_post_meta(get_the_ID(), 'doctor_title', true)); ?></p>
                  <h4 class="qa-doctor-name"><?php the_title(); ?></h4>
              </a>
              <div class="qa-doctor-desc"><?php the_excerpt(); ?></div>
              <div class="qa-doctor-button">
                  <a href="#" class="qa-doctor-cta"><?php _e('Bác sĩ tư vấn','nex2tek-qa'); ?></a>
              </div>
          </div>
      <?php
          $i++;
      endwhile;
      wp_reset_postdata(); ?>
  </div>

  <?php if ($i > 4): ?>
      <div class="qa-doctor-toggle text-center mt-3">
          <button class="qa-doctor-toggle-btn"><?php _e('Xem thêm','nex2tek-qa'); ?></button>
      </div>
  <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.querySelector('.qa-doctor-toggle-btn');
    const hiddenCards = document.querySelectorAll('.qa-doctor-hidden');
    let expanded = false;

    toggleBtn?.addEventListener('click', function () {
        hiddenCards.forEach(card => card.classList.toggle('qa-doctor-visible'));
        expanded = !expanded;
        toggleBtn.textContent = expanded ? <?php _e('Thu gọn','nex2tek-qa'); ?> : <?php _e('Xem thêm','nex2tek-qa'); ?>;
    });
});
</script>