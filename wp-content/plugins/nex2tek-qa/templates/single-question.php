<?php get_header();
increase_question_view_count(get_the_ID());
$userName = get_post_meta(get_the_ID(), 'qa_name', true) ?: 'Người ẩn danh';
$viewCount = number_format((int) get_post_meta(get_the_ID(), 'view_count', true));
$createdDate = get_the_date('d/m/Y', get_the_ID());
$answer = get_post_meta(get_the_ID(), '_answer', true);
?>

<div class="qa-container container mt-4">
    <?php nex2tek_breadcrumb(); ?>
    <div class="qa-row row">
        <div class="qa-col qa-sidebar-left">
            <?php echo do_shortcode('[nex2tek_qa_question_category]'); ?>
        </div>
        <div class="qa-col qa-main-form knhd_main">
            <main class="qa-single">
                <section class="question_knhd_1_0_1">
                    <article class=question_knhd_1_0_1__item>
                        <h1 class="question_knhd_1_0_1__title"><?php the_title(); ?></h1>
                        <div class="question_knhd_1_0_1__meta">
                            <div class="question_knhd_1_0_1__info">
                                <div class="question_knhd_1_0_1__avatar">
                                    <img width="40" height="40" src="<?php echo plugins_url('assets/images/avatar.png', __DIR__); ?>" alt="<?= $userName ?>">
                                </div>
                                <div class="question_knhd_1_0_1__name"><?= $userName ?></div>
                            </div>
                            <div class="question_knhd_1_0_1__date"><?php nex2tek_echo('Đã hỏi','nex2tek-qa'); ?>: <span><?= $createdDate ?></span></div>
                        </div>
                        <h2 class="question_knhd_1_0_1__content"><?php the_content(); ?></h2>
                        <div class="question_knhd_1_0_1__action">
                            <div>
                                <a href="#comment-section" class="question_knhd_1_0_1__comment">
                                    <span class="question_knhd_1_0_1__icon question_knhd_1_0_1__icon--1"></span>
                                    <span><?= get_comments_number() ?> <?php nex2tek_echo('bình luận','nex2tek-qa'); ?></span>
                                </a>
                                <a href="#" class="question_knhd_1_0_1__view">
                                    <span class="question_knhd_1_0_1__icon question_knhd_1_0_1__icon--2"></span>
                                    <span><?= $viewCount ?> <?php nex2tek_echo('lượt xem', 'nex2tek-qa'); ?></span>
                                </a>
                            </div>
                        </div>
                    </article>
                </section>
                <section class="answer_knhd_1_0_0">
                    <article class="answer_knhd_1_0_0__item">
                        <div class="answer_knhd_1_0_0__content">
                            <?= $answer ?>
                        </div>
                        <div class="answer_knhd_1_0_0__action helpful-buttons" data-post-id="18458" data-user-vote="">
                            <a href="#comment-section" class="answer_knhd_1_0_0__reply"><span class="answer_knhd_1_0_0__icon answer_knhd_1_0_0__icon--3"></span><?php nex2tek_echo('Trả lời', 'nex2tek-qa'); ?> </a>
                        </div>
                    </article>
                </section>
                <div id="comment-section">
                    <?php
                    if (comments_open() || get_comments_number()) {
                        comments_template();
                    }
                    ?>
                </div>
            </main>
        </div>
        <div class="qa-col qa-sidebar-right">
            <?php echo do_shortcode('[nex2tek_qa_question_view]'); ?>
            <?php echo do_shortcode('[nex2tek_qa_question_comment]'); ?> 
        </div>
    </div>
</div>

<?php get_footer(); ?>
