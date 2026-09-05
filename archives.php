<?php
/**
 * 按时间浏览文章
 *
 * @package custom
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('header.php');
?>
<section class="container archive-page">
    <header class="archive-header">
        <p class="eyebrow">文章归档</p>
        <h1><?php $this->title(); ?></h1>
        <p><?php echo nl2br(lumino_escape($this->excerpt ?: '按时间回看每一次记录。')); ?></p>
    </header>
    <div class="archive-list">
        <?php \Widget\Contents\Post\Recent::alloc('pageSize=' . (int) lumino_theme_option('archivePostLimit', '100'))->to($posts); ?>
        <?php while ($posts->next()): ?><a class="archive-row" href="<?php $posts->permalink(); ?>"><time><?php $posts->date('Y.m.d'); ?></time><strong><?php $posts->title(); ?></strong><span>↗</span></a><?php endwhile; ?>
    </div>
</section>
<?php $this->need('footer.php'); ?>
