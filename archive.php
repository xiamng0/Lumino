<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>
<section class="container archive-page"><header class="archive-header"><p class="eyebrow">ARCHIVE</p><h1><?php $this->archiveTitle('%s'); ?></h1><p>按时间回看每一次记录。</p></header><div class="archive-list"><?php while ($this->next()): ?><a class="archive-row" href="<?php $this->permalink(); ?>"><time><?php $this->date('Y.m.d'); ?></time><strong><?php $this->title(); ?></strong><span>↗</span></a><?php endwhile; ?></div><div class="pagination"><?php $this->pageLink('更早的文章 →', 'next'); ?><?php $this->pageLink('← 更新的文章', 'prev'); ?></div></section>
<?php $this->need('footer.php'); ?>
