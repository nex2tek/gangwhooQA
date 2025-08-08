<?php
$current_lang = get_current_lang();

// Get all published doctors
$args = [
    'post_type'      => 'doctor',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
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

$doctors = new WP_Query($args);

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
                        } else { 
                            echo '<img src="' . plugins_url('assets/images/avatar.png', __DIR__) . '" alt="avatar">'; 
                        } ?>
                  </div>
                  <p class="qa-doctor-title"><?php echo esc_html(get_post_meta(get_the_ID(), 'doctor_title', true)); ?></p>
                  <h4 class="qa-doctor-name"><?php the_title(); ?></h4>
              </a>
              <div class="qa-doctor-desc"><?php the_excerpt(); ?></div>
              <div class="qa-doctor-button">
                  <a href="<?php the_permalink(); ?>" class="qa-doctor-cta"><?php nex2tek_echo('Bác sĩ tư vấn','nex2tek-qa'); ?></a>
              </div>
          </div>
      <?php
          $i++;
      endwhile;
      wp_reset_postdata(); ?>
  </div>

  <?php if ($i > 4): ?>
      <div class="qa-doctor-toggle text-center">
          <button class="qa-doctor-toggle-btn" data-expanded-text="<?php nex2tek_echo('Thu gọn', 'nex2tek-qa'); ?>" 
          data-collapsed-text="<?php nex2tek_echo('Xem thêm', 'nex2tek-qa'); ?>"><?php nex2tek_echo('Xem thêm','nex2tek-qa'); ?></button>
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

        const expandedText = toggleBtn.dataset.expandedText;
        const collapsedText = toggleBtn.dataset.collapsedText;

        toggleBtn.textContent = expanded ? expandedText : collapsedText;
    });
});
</script>