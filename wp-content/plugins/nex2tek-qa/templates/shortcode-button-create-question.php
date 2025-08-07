<?php
$page_id = get_page_by_path('gui-cau-hoi');
$page_id = $page_id ? $page_id->ID : 0;
$link = function_exists('pll_get_post') ? get_permalink(pll_get_post($page_id)) : get_permalink($page_id);
?>
<div class="send-question-button">
  <a href="<?php echo $link; ?>" class="btn btn-primary"><?php nex2tek_echo('Gui cau hoi', 'nex2tek-qa'); ?></a>
</div>
