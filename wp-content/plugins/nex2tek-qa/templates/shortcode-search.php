<?php $current_query = !empty($_GET['qa-search']) ? sanitize_text_field($_GET['qa-search']) : ''; ?>
<div class="qa-search qa-form-wrapper">
  <h2 class="qa-search-title"><?php nex2tek_echo('CÂU HỎI CỦA BẠN LÀ GÌ', 'nex2tek-qa');?>?</h2>
  <p class="qa-search-sub"><?php nex2tek_echo('Nhận câu trả lời ngay lập tức', 'nex2tek-qa');?></p>
  <form role="search" method="get" action="" id="qa-search-form">
    <div class="input-group">
      <input type="text" name="qa-search" placeholder="<?php nex2tek_echo('Gõ câu hỏi', 'nex2tek-qa'); ?>..." value="<?php echo $current_query; ?>">
      <button type="submit"><i class="bi bi-search"></i></button>
    </div>
  </form>
  <?php if(!empty($current_query)): ?>
  <div class="qa-search-result">
    <?php
      $count_query = nex2tek_get_questions(['posts_per_page' => -1, 'fields' => 'ids']);
      $total_results = $count_query->post_count;
    ?>
    <p><?php nex2tek_echo('Có', 'nex2tek-qa'); ?> <span><?php echo $total_results; ?></span>  <?php nex2tek_echo('kết quả tìm kiếm', 'nex2tek-qa'); ?> "<?php echo $current_query; ?>"<p>
  </div>
  <?php endif; ?>
</div>

